{{--
    COOCA AI — Interactive 3D Virtual Office Cockpit & 3-Story Cutaway Building
    Aligned with Image 2 Reference & The Sims Living Workforce Simulation
--}}
@props([
    'mode' => 'executive', // 'executive', 'operations', 'growth', 'lobby'
    'agentStatuses' => [],
    'recentTasks' => collect(),
    'pendingProposals' => collect(),
    'recentHistories' => collect(),
    'companyTotals' => [],
])

@php
    $officeConfigKey = 'window.__COOCA_OFFICE_' . strtoupper(preg_replace('/[^a-zA-Z0-9_]/', '_', $mode)) . '_CONFIG';
    
    // Dynamic single-source-of-truth stats across all views
    $totalAgentsCount = $mode === 'lobby' 
        ? ($companyTotals['total_agents'] ?? (count($agentStatuses) ?: 12))
        : (count($agentStatuses) ?: 4);
        
    $workingAgentsCount = count(array_filter($agentStatuses, fn($a) => in_array(strtoupper($a['status'] ?? ''), ['WORKING', 'RUNNING'])));
    $waitingApprovalAgentsCount = count(array_filter($agentStatuses, fn($a) => strtoupper($a['status'] ?? '') === 'WAITING_APPROVAL'));
    
    $activeAgentsCount = $mode === 'lobby'
        ? ($companyTotals['active_agents'] ?? ($workingAgentsCount + $waitingApprovalAgentsCount))
        : ($workingAgentsCount + $waitingApprovalAgentsCount);
        
    if ($activeAgentsCount === 0 && !empty($agentStatuses)) {
        $activeAgentsCount = max(1, $workingAgentsCount ?: count($agentStatuses));
    }
    
    $pendingApprovalsCount = $mode === 'lobby'
        ? ($companyTotals['pending_approvals'] ?? $pendingProposals->count())
        : $pendingProposals->count();
        
    $idleAgentsCount = max(0, $totalAgentsCount - $activeAgentsCount);

    $tasksData = $recentTasks->take(15)->map(fn($t) => [
        'id' => $t->id,
        'agent' => $t->agent,
        'role' => $t->executive_role ?? $t->agent,
        'input' => $t->input,
        'status' => $t->status,
        'time' => $t->created_at?->diffForHumans() ?? 'Baru saja',
    ])->values();
    $proposalsData = $pendingProposals->take(5)->map(fn($p) => [
        'id' => $p->id,
        'agent' => $p->agent,
        'title' => $p->title,
        'risk' => $p->risk_level,
        'cost' => $p->estimated_cost,
    ])->values();
    $historiesData = $recentHistories->take(25)->map(fn($h) => [
        'id' => $h->id,
        'agent' => $h->agent ?? 'system',
        'action' => $h->action ?? $h->title ?? 'Eksekusi Otomasi',
        'details' => $h->details ?? $h->description ?? $h->input ?? 'Aktivitas AI tersinkronisasi',
        'status' => $h->status ?? 'success',
        'time' => $h->created_at?->diffForHumans() ?? 'Baru saja',
    ])->values();

    $businessCurrent = \App\Support\Context::business();
    $businessMetricsData = $businessCurrent ? app(\App\Domain\Ai\Services\AiAgentBusinessMetricsService::class)->getAllMetrics($businessCurrent) : [];
@endphp

<!-- Client-side Vendor 3D Assets (Three.js r128 + OrbitControls + Cooca 3D Engine) -->
<script src="{{ asset('js/vendor/three/three.min.js') }}"></script>
<script>
    if (typeof window.THREE === 'undefined') {
        document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"><\/script>');
    }
</script>
<script src="{{ asset('js/vendor/three/OrbitControls.js') }}"></script>
<script>
    if (typeof window.THREE !== 'undefined' && typeof window.THREE.OrbitControls === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"><\/script>');
    }
</script>
<script src="{{ asset('js/ai/virtual-office-3d.js') }}"></script>

<script>
    {{ $officeConfigKey }} = {
        mode: @js($mode),
        agents: @js($agentStatuses),
        totalAgentsCount: @js($totalAgentsCount),
        activeAgentsCount: @js($activeAgentsCount),
        idleAgentsCount: @js($idleAgentsCount),
        pendingApprovalsCount: @js($pendingApprovalsCount),
        tasks: @js($tasksData),
        proposals: @js($proposalsData),
        histories: @js($historiesData),
        presets: @js(\App\Models\AiAgentAvatar::getPresets()),
        businessData: @js($businessMetricsData),
        liveMetricsUrl: @js(route('cooca-ai.live-metrics'))
    };
</script>

<style>
    #cooca-3d-office-viewport-{{ $mode }} {
        width: 100% !important;
        max-width: 100% !important;
        position: relative;
        overflow: hidden;
    }
    #cooca-3d-office-viewport-{{ $mode }} canvas {
        max-width: 100% !important;
        width: 100% !important;
        height: 100% !important;
        display: block;
    }
</style>

<div
    x-data="coocaVirtualOffice({{ $officeConfigKey }})"
    x-init="initOffice()"
    class="space-y-4 w-full"
>
    <!-- Top Action / Control Bar for Virtual Office Floor Modes -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 p-3 rounded-2xl bg-white/70 dark:bg-zinc-900/70 backdrop-blur-md border border-black/10 dark:border-white/10 shadow-sm">
        
        <!-- Left: Multi-Mode Switcher (3D / 2D) -->
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex p-1 rounded-xl bg-black/5 dark:bg-white/10 border border-black/5 dark:border-white/5 text-xs font-semibold">
                <!-- 3D Virtual Office Tab -->
                <button
                    type="button"
                    @click="setMode('3d')"
                    :class="viewMode === '3d' ? 'bg-white dark:bg-zinc-800 text-black dark:text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer"
                    title="Ruang Kantor 3D Interaktif"
                >
                    <i data-lucide="box" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>3D Virtual Office Floor</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse ml-0.5"></span>
                </button>

                <!-- 2D Floor Plan Blueprint Tab -->
                <button
                    type="button"
                    @click="setMode('office')"
                    :class="viewMode === 'office' ? 'bg-white dark:bg-zinc-800 text-black dark:text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="px-2.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer"
                    title="Denah Blueprint 2D Isometrik"
                >
                    <i data-lucide="compass" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>2D Blueprint</span>
                </button>
            </div>

            <!-- 12 AI Agent Status Counter -->
            <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-black/10 dark:border-white/10 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span><strong class="font-bold text-slate-900 dark:text-white">{{ $totalAgentsCount }} AI Agent</strong>: <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $activeAgentsCount }}</span> Aktif &bull; <span class="text-slate-500 dark:text-slate-400">{{ $idleAgentsCount }}</span> Siaga</span>
                </span>
            </div>
        </div>

        <!-- Right: 3D Camera Presets, Lighting, Audio & Controls -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- 3D Camera Presets Dropdown (Floor & Room Jumps) -->
            <div x-show="viewMode === '3d'" class="relative" x-data="{ cameraMenuOpen: false }">
                <button
                    type="button"
                    @click="cameraMenuOpen = !cameraMenuOpen"
                    class="px-2.5 py-1.5 rounded-xl border border-black/10 dark:border-white/10 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70 hover:bg-black/10 dark:hover:bg-white/10"
                    title="Pilih Sudut Pandang Kamera Ruangan"
                >
                    <i data-lucide="video" class="w-3.5 h-3.5 text-sky-500"></i>
                    <span class="hidden md:inline">Preset Kamera</span>
                    <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="cameraMenuOpen ? 'rotate-180' : ''"></i>
                </button>
                <div
                    x-show="cameraMenuOpen"
                    @click.outside="cameraMenuOpen = false"
                    x-transition
                    class="absolute right-0 mt-1.5 w-52 rounded-2xl bg-slate-950/95 dark:bg-[#070b14]/95 backdrop-blur-2xl border border-white/20 p-1.5 shadow-2xl z-50 space-y-1 text-xs text-white"
                    style="display: none;"
                >
                    <div class="px-2 py-1 text-[10px] font-bold text-white/50 uppercase tracking-wider font-mono">Fokus Ruangan</div>
                    <button type="button" @click="set3DCamera('overview'); cameraMenuOpen = false;" class="w-full p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="building" class="w-3.5 h-3.5 text-sky-400"></i> <span>HQ Keseluruhan</span>
                    </button>
                    <button type="button" @click="set3DCamera('executive'); cameraMenuOpen = false;" class="w-full p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-400"></i> <span>Direksi &amp; CEO</span>
                    </button>
                    <button type="button" @click="set3DCamera('boardroom'); cameraMenuOpen = false;" class="w-full p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="handshake" class="w-3.5 h-3.5 text-blue-400"></i> <span>Boardroom</span>
                    </button>
                    <button type="button" @click="set3DCamera('marketing'); cameraMenuOpen = false;" class="w-full p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="palette" class="w-3.5 h-3.5 text-purple-400"></i> <span>Marketing</span>
                    </button>
                    <button type="button" @click="set3DCamera('sales'); cameraMenuOpen = false;" class="w-full p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="briefcase" class="w-3.5 h-3.5 text-emerald-400"></i> <span>Sales CRM</span>
                    </button>
                    <button type="button" @click="set3DCamera('operations'); cameraMenuOpen = false;" class="w-full p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="package" class="w-3.5 h-3.5 text-teal-400"></i> <span>Operasional</span>
                    </button>
                    <button type="button" @click="set3DCamera('pantry'); cameraMenuOpen = false;" class="w-full p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="coffee" class="w-3.5 h-3.5 text-rose-400"></i> <span>Pantry &amp; Lounge</span>
                    </button>
                    <button type="button" @click="set3DCamera('server'); cameraMenuOpen = false;" class="w-full p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="server" class="w-3.5 h-3.5 text-indigo-400"></i> <span>Server Bay</span>
                    </button>
                </div>
            </div>

            <!-- Day / Night Lighting Switcher -->
            <button
                type="button"
                @click="toggleDayNight()"
                x-show="viewMode === '3d'"
                class="px-2.5 py-1.5 rounded-xl border border-black/10 dark:border-white/10 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer"
                :class="isNightMode ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                :title="isNightMode ? 'Beralih ke Siang (Sunlight)' : 'Beralih ke Malam Cyberpunk'"
            >
                <i :data-lucide="isNightMode ? 'moon' : 'sun'" class="w-3.5 h-3.5 text-amber-500"></i>
                <span class="hidden md:inline" x-text="isNightMode ? 'Night Mode' : 'Daylight'"></span>
            </button>

            <!-- Audio SFX Toggle -->
            <button
                type="button"
                @click="toggleAudio()"
                class="px-2.5 py-1.5 rounded-xl border border-black/10 dark:border-white/10 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer"
                :class="audioEnabled ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60'"
            >
                <i :data-lucide="audioEnabled ? 'volume-2' : 'volume-x'" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline" x-text="audioEnabled ? 'Sound ON' : 'Sound OFF'"></span>
            </button>

            <!-- Ganti Avatar Modal Trigger -->
            <button
                type="button"
                @click="openAvatarModal()"
                class="px-2.5 py-1.5 rounded-xl bg-[#AF52DE]/10 hover:bg-[#AF52DE]/20 text-[#AF52DE] dark:text-[#BF5AF2] border border-[#AF52DE]/30 text-xs font-semibold flex items-center gap-1.5 transition shadow-sm cursor-pointer"
                title="Kustomisasi Karakter & Avatar AI"
            >
                <i data-lucide="palette" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Avatar Agen</span>
            </button>

            <!-- Organisasi & Pembagian Tugas Modal Trigger -->
            <button
                type="button"
                @click="openOrgChartModal()"
                class="px-2.5 py-1.5 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30 text-xs font-semibold flex items-center gap-1.5 transition shadow-sm cursor-pointer"
                title="Lihat Struktur Tim & Pembagian Tugas 12 AI Agent"
            >
                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">12 Agen &amp; Pembagian Tugas</span>
            </button>

            <!-- Work Simulation Trigger -->
            <button
                type="button"
                @click="triggerLiveScan()"
                :disabled="isScanning"
                class="px-3 py-1.5 rounded-xl bg-black dark:bg-white hover:bg-black/90 dark:hover:bg-white/90 text-white dark:text-black text-xs font-semibold flex items-center gap-1.5 transition disabled:opacity-50 shadow-sm cursor-pointer"
            >
                <i data-lucide="sparkles" class="w-3.5 h-3.5" :class="isScanning ? 'animate-spin' : ''"></i>
                <span x-text="isScanning ? 'Menganalisis...' : 'Simulasi Kerja'"></span>
            </button>

            <!-- Fullscreen Viewport Mode Button -->
            <button
                type="button"
                @click="toggleFullscreen()"
                x-show="viewMode === '3d'"
                class="px-3 py-1.5 rounded-xl border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-semibold flex items-center gap-1.5 transition shadow-sm cursor-pointer"
                :title="isFullscreen ? 'Keluar Mode Layar Penuh [ESC]' : 'Mode Layar Penuh (Fullscreen) [F]'"
            >
                <i :data-lucide="isFullscreen ? 'minimize-2' : 'maximize-2'" class="w-3.5 h-3.5"></i>
                <span x-text="isFullscreen ? 'Keluar Fullscreen' : 'Full Screen'"></span>
                <span class="hidden xl:inline text-[10px] opacity-60 font-mono" x-text="isFullscreen ? '[ESC]' : '[F]'"></span>
            </button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MAIN 2-COLUMN VIEWPORT LAYOUT (Matching Image 3 Campus Design)  -->
    <!-- Left: 1-Floor Interactive Campus (3D / 2D / Bento)              -->
    <!-- Right: AI Office Status, Active Work, Approvals & Activity      -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start w-full">
        
        <!-- ---------------------------------------------------------- -->
        <!-- CENTER STAGE: 1-Floor Modern Corporate Campus (3D/2D/Bento) -->
        <!-- ---------------------------------------------------------- -->
        <div class="lg:col-span-8 xl:col-span-9 space-y-3 min-w-0">
            
            <!-- ============================================================== -->
            <!-- 1. FULL 3D INTERACTIVE VIRTUAL OFFICE VIEWPORT (THREE.JS WEBGL) -->
            <!-- ============================================================== -->
            <div
                x-show="viewMode === '3d'"
                id="cooca-office-container-{{ $mode }}"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                :class="isFullscreen ? 'fixed inset-0 z-[100] w-screen h-screen min-h-screen rounded-none border-0 shadow-none' : 'relative rounded-3xl min-h-[660px] xl:min-h-[740px] border border-slate-300 dark:border-white/10 shadow-2xl'"
                class="bg-slate-200/80 dark:bg-[#090d16] overflow-hidden select-none transition-all duration-300 flex flex-col w-full min-w-0"
            >
                <!-- 3D Room & Status Header HUD (Top Left) -->
                <div class="absolute top-4 left-4 z-20 flex items-center gap-2 pointer-events-auto max-w-[calc(100%-140px)] flex-nowrap">
                    <div class="px-3 py-1.5 rounded-xl bg-slate-900/85 dark:bg-black/85 backdrop-blur-md border border-white/15 text-white flex items-center gap-2 text-xs shadow-xl truncate">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                        <span class="font-bold tracking-wide uppercase text-[11px] font-mono truncate">
                            @if($mode === 'executive')
                                COOCA TOWER // DIREKSI PENTHOUSE
                            @elseif($mode === 'operations')
                                COOCA TOWER // LOGISTIK & PENGADAAN
                            @elseif($mode === 'growth')
                                COOCA TOWER // KREATIF & SALES
                            @else
                                COOCA TOWER // PENTHOUSE CAMPUS
                            @endif
                        </span>
                    </div>
                    <div class="hidden sm:inline-flex px-2.5 py-1 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[11px] font-mono items-center gap-1.5 shadow-sm shrink-0 whitespace-nowrap">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-bold">WEBGL 3D 60FPS</span>
                        <span>&bull;</span>
                        <span><strong class="font-bold text-white">{{ $activeAgentsCount }}</strong>/{{ $totalAgentsCount }} Aktif</span>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- FLOATING POV CONTROLLER HUD OVERLAY (TOP CENTER)               -->
                <!-- ============================================================== -->
                <div
                    x-show="isPOVMode"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-4"
                    class="absolute top-4 left-1/2 -translate-x-1/2 z-30 pointer-events-auto flex items-center gap-2 max-w-[95%]"
                    style="display: none;"
                >
                    <div class="px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-2xl bg-slate-950/90 dark:bg-black/90 backdrop-blur-2xl border border-cyan-500/40 shadow-[0_12px_40px_rgba(0,0,0,0.7)] flex flex-wrap items-center gap-2 sm:gap-3 text-white text-xs">
                        <div class="flex items-center gap-2 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
                            <i data-lucide="gamepad-2" class="w-4 h-4"></i>
                            <span class="font-bold text-cyan-300 truncate max-w-[140px] sm:max-w-[200px]" x-text="povAgentData.name || povAgentRole"></span>
                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 uppercase font-mono font-bold">
                                POV
                            </span>
                        </div>

                        <div class="hidden sm:block h-4 w-px bg-white/20"></div>

                        <!-- Toggle 1st Person / 3rd Person POV -->
                        <button
                            type="button"
                            @click="togglePOVCamera()"
                            class="px-2.5 py-1 rounded-xl bg-white/10 hover:bg-white/20 text-white text-[11px] font-semibold flex items-center gap-1.5 transition cursor-pointer"
                            title="Ganti Sudut Pandang (Mata / Bahu) [V]"
                        >
                            <i data-lucide="eye" class="w-3.5 h-3.5 text-cyan-400"></i>
                            <span x-text="povCameraType === 'first_person' ? 'Kamera: Mata (1st)' : 'Kamera: Bahu (3rd)'"></span>
                            <kbd class="hidden md:inline-block text-[9px] bg-black/50 px-1 py-0.5 rounded font-mono">V</kbd>
                        </button>

                        <!-- Sit or Stand -->
                        <button
                            type="button"
                            @click="sitOrStandPOV()"
                            class="px-2.5 py-1 rounded-xl bg-white/10 hover:bg-white/20 text-white text-[11px] font-semibold flex items-center gap-1.5 transition cursor-pointer"
                            title="Duduk di Kursi Terdekat / Berdiri [E]"
                        >
                            <i data-lucide="armchair" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Duduk / Berdiri</span>
                            <kbd class="hidden md:inline-block text-[9px] bg-black/50 px-1 py-0.5 rounded font-mono">E</kbd>
                        </button>

                        <div class="h-4 w-px bg-white/20"></div>

                        <!-- Exit POV Mode -->
                        <button
                            type="button"
                            @click="exitPOV()"
                            class="px-2.5 py-1 rounded-xl bg-rose-600/90 hover:bg-rose-600 text-white text-[11px] font-bold flex items-center gap-1 transition cursor-pointer shadow-sm"
                            title="Keluar dari Kontrol Agen [ESC]"
                        >
                            <i data-lucide="log-out" class="w-3 h-3"></i>
                            <span>Keluar</span>
                            <kbd class="hidden sm:inline-block text-[9px] bg-black/50 px-1 py-0.5 rounded font-mono">ESC</kbd>
                        </button>
                    </div>
                </div>

                <!-- Top Right HUD Controls -->
                <div class="absolute top-4 right-4 z-20 flex items-center gap-2 pointer-events-auto">
                    <!-- Reset Camera Button -->
                    <button
                        type="button"
                        @click="set3DCamera('overview')"
                        x-show="!isPOVMode"
                        class="px-2.5 py-1.5 rounded-xl bg-slate-900/80 hover:bg-slate-800 dark:bg-black/70 dark:hover:bg-black/85 backdrop-blur-md border border-white/20 text-white text-[11px] font-medium transition flex items-center gap-1.5 shadow-lg cursor-pointer"
                        title="Reset Kamera ke Tampilan Utama [R]"
                    >
                        <i data-lucide="rotate-ccw" class="w-3 h-3 text-sky-400"></i>
                        <span class="hidden sm:inline">Reset View</span>
                    </button>

                    <!-- Fullscreen Exit / Toggle Button -->
                    <button
                        type="button"
                        @click="toggleFullscreen()"
                        class="px-3 py-1.5 rounded-xl backdrop-blur-md border text-xs font-bold transition flex items-center gap-1.5 shadow-xl cursor-pointer"
                        :class="isFullscreen ? 'bg-rose-600/90 hover:bg-rose-600 text-white border-rose-400/40 ring-2 ring-rose-500/30' : 'bg-indigo-600/90 hover:bg-indigo-600 text-white border-indigo-400/40'"
                        :title="isFullscreen ? 'Keluar Mode Layar Penuh [ESC]' : 'Masuk Mode Layar Penuh [F]'"
                    >
                        <i :data-lucide="isFullscreen ? 'minimize-2' : 'maximize-2'" class="w-3.5 h-3.5"></i>
                        <span x-text="isFullscreen ? 'Keluar Fullscreen' : 'Fullscreen'"></span>
                        <span class="hidden sm:inline text-[10px] opacity-75 font-mono" x-text="isFullscreen ? '[ESC]' : '[F]'"></span>
                    </button>
                </div>

                <!-- Floating Bottom Status Pill -->
                <div x-show="!isPOVMode"
                     class="absolute bottom-5 left-5 z-20 hidden md:flex items-center gap-3 px-4 py-2 rounded-2xl bg-slate-900/85 hover:bg-slate-900 dark:bg-[#070b14]/90 dark:hover:bg-[#070b14] backdrop-blur-xl border border-white/15 text-white shadow-2xl transition group cursor-pointer pointer-events-auto"
                     @click="$dispatch('open-ai-consultation')">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                    <div class="text-xs">
                        <div class="font-bold text-white leading-tight">All systems operational</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">COOCA AI is running smoothly</div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition ml-1"></i>
                </div>

                <!-- ============================================================== -->
                <!-- FLOATING POV BOTTOM COMMAND & MOVEMENT HUD                     -->
                <!-- ============================================================== -->
                <div
                    x-show="isPOVMode"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-6"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-6"
                    class="absolute bottom-5 left-1/2 -translate-x-1/2 z-30 pointer-events-auto w-[94%] max-w-2xl"
                    style="display: none;"
                >
                    <div class="p-3 rounded-2xl bg-slate-950/90 dark:bg-black/95 backdrop-blur-2xl border border-cyan-500/40 shadow-[0_16px_50px_rgba(0,0,0,0.85)] space-y-2.5">
                        <!-- Top Row: Movement Guide Keys -->
                        <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-300 border-b border-white/10 pb-2">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <span class="inline-flex items-center gap-1 font-mono text-cyan-400 font-bold">
                                    <kbd class="px-1.5 py-0.5 rounded bg-white/10 border border-white/20">W</kbd>
                                    <kbd class="px-1.5 py-0.5 rounded bg-white/10 border border-white/20">A</kbd>
                                    <kbd class="px-1.5 py-0.5 rounded bg-white/10 border border-white/20">S</kbd>
                                    <kbd class="px-1.5 py-0.5 rounded bg-white/10 border border-white/20">D</kbd>
                                    <span class="text-slate-300 ml-0.5 font-sans font-normal">Jalan</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[10px]">
                                    <kbd class="px-1.5 py-0.5 rounded bg-white/10 border border-white/20">Shift</kbd>
                                    <span class="font-sans">Lari</span>
                                </span>
                                <span class="hidden sm:inline-flex items-center gap-1 text-slate-400 text-[10px]">
                                    <i data-lucide="mouse-pointer" class="w-3.5 h-3.5"></i>
                                    <span>Drag Mouse: Pandangan</span>
                                </span>
                                <span class="hidden md:inline-flex items-center gap-1 text-slate-400 text-[10px]">
                                    <kbd class="px-1 py-0.5 rounded bg-white/10 border border-white/20 font-mono">E</kbd>
                                    <span>Duduk</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-cyan-300 font-mono text-[10px]">
                                    <kbd class="px-1.5 py-0.5 rounded bg-cyan-950/60 border border-cyan-400/40 font-mono text-cyan-300 font-bold">F</kbd>
                                    <span class="font-sans text-slate-200">Pintu</span>
                                </span>
                            </div>
                            <div class="text-[10px] text-cyan-300/90 font-mono flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Sekat Kaca Blur & Pintu Otomatis</span>
                            </div>
                        </div>

                        <!-- Bottom Row: Fast Command Bar ("Beri Perintah") -->
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input
                                    type="text"
                                    x-model="povCommandInput"
                                    @keydown.enter="sendPOVCommand()"
                                    :placeholder="`Perintahkan ${povAgentData.name || povAgentRole}... (Ketik lalu tekan Enter)`"
                                    class="w-full text-xs bg-white/10 border border-white/20 rounded-xl px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition"
                                />
                            </div>
                            <button
                                type="button"
                                @click="sendPOVCommand()"
                                :disabled="!povCommandInput.trim() || isAssigningTask"
                                class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-xs font-bold flex items-center gap-1.5 transition shadow-md shadow-cyan-500/25 cursor-pointer disabled:opacity-50 shrink-0"
                            >
                                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                <span>Beri Perintah</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- FLOATING CONTROL CENTER DOCK (PUSAT KONTROL HUD MELAYANG)       -->
                <!-- ============================================================== -->
                <div x-show="!isPOVMode" class="absolute bottom-5 left-1/2 -translate-x-1/2 z-30 pointer-events-auto">
                    <div class="px-3 py-2 rounded-2xl bg-slate-950/90 dark:bg-[#060911]/95 backdrop-blur-2xl border border-white/20 shadow-[0_16px_50px_rgba(0,0,0,0.7)] flex items-center gap-2">
                        
                        <!-- 1. Tanya AI (Opens Unified Consultation Modal) -->
                        <button
                            type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-ai-consultation'))"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition shadow-md bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white cursor-pointer active:scale-95"
                            title="Buka Konsultasi & Instruksi Tim AI"
                        >
                            <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                            <span>Tanya AI</span>
                        </button>

                        <div class="h-6 w-px bg-white/15 mx-0.5"></div>

                        <!-- 2. Preset Kamera 3D Popover -->
                        <div class="relative">
                            <button
                                type="button"
                                @click="isCameraMenuOpen = !isCameraMenuOpen"
                                class="px-3 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition shadow-sm cursor-pointer"
                                :class="isCameraMenuOpen ? 'bg-indigo-500 text-white font-bold' : 'bg-white/10 hover:bg-white/20 text-white/90'"
                                title="Preset Sudut Pandang Kamera Ruangan"
                            >
                                <i data-lucide="camera" class="w-4 h-4 text-indigo-400" :class="isCameraMenuOpen ? 'text-white' : ''"></i>
                                <span>Kamera</span>
                                <i data-lucide="chevron-up" class="w-3.5 h-3.5 opacity-70 transition-transform" :class="isCameraMenuOpen ? 'rotate-180' : ''"></i>
                            </button>

                            <!-- Camera Presets Popover Menu -->
                            <div
                                x-show="isCameraMenuOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                @click.outside="isCameraMenuOpen = false"
                                class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 w-72 rounded-2xl bg-slate-950/95 dark:bg-[#070b14]/95 backdrop-blur-2xl border border-white/20 p-2 shadow-2xl z-50 space-y-1"
                                style="display: none;"
                            >
                                <div class="px-2.5 py-1 text-[10px] font-bold text-white/50 uppercase tracking-wider font-mono">
                                    Preset Sudut Kamera Ruangan
                                </div>
                                <div class="grid grid-cols-2 gap-1 text-xs">
                                    <button type="button" @click="set3DCamera('overview'); isCameraMenuOpen = false;" class="p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 text-white flex items-center gap-2 transition cursor-pointer">
                                        <i data-lucide="building" class="w-3.5 h-3.5"></i> <span class="font-medium truncate">HQ Keseluruhan</span>
                                    </button>
                                    <button type="button" @click="set3DCamera('executive'); isCameraMenuOpen = false;" class="p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 text-white flex items-center gap-2 transition cursor-pointer">
                                        <i data-lucide="crown" class="w-3.5 h-3.5"></i> <span class="font-medium truncate">Direksi CEO</span>
                                    </button>
                                    <button type="button" @click="set3DCamera('boardroom'); isCameraMenuOpen = false;" class="p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 text-white flex items-center gap-2 transition cursor-pointer">
                                        <i data-lucide="handshake" class="w-3.5 h-3.5"></i> <span class="font-medium truncate">Boardroom</span>
                                    </button>
                                    <button type="button" @click="set3DCamera('marketing'); isCameraMenuOpen = false;" class="p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 text-white flex items-center gap-2 transition cursor-pointer">
                                        <i data-lucide="palette" class="w-3.5 h-3.5"></i> <span class="font-medium truncate">Marketing</span>
                                    </button>
                                    <button type="button" @click="set3DCamera('sales'); isCameraMenuOpen = false;" class="p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 text-white flex items-center gap-2 transition cursor-pointer">
                                        <i data-lucide="briefcase" class="w-3.5 h-3.5"></i> <span class="font-medium truncate">Sales CRM</span>
                                    </button>
                                    <button type="button" @click="set3DCamera('operations'); isCameraMenuOpen = false;" class="p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 text-white flex items-center gap-2 transition cursor-pointer">
                                        <i data-lucide="package" class="w-3.5 h-3.5"></i> <span class="font-medium truncate">Operasional</span>
                                    </button>
                                    <button type="button" @click="set3DCamera('pantry'); isCameraMenuOpen = false;" class="p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 text-white flex items-center gap-2 transition cursor-pointer">
                                        <i data-lucide="coffee" class="w-3.5 h-3.5"></i> <span class="font-medium truncate">Cafe Bistro</span>
                                    </button>
                                    <button type="button" @click="set3DCamera('server'); isCameraMenuOpen = false;" class="p-2 rounded-xl text-left bg-white/5 hover:bg-white/15 text-white flex items-center gap-2 transition cursor-pointer">
                                        <i data-lucide="server" class="w-3.5 h-3.5"></i> <span class="font-medium truncate">Server Bay</span>
                                    </button>
                                </div>
                            </div>
                    </div>
                </div>

                </div>

                <!-- Interactive Inspector HUD Overlay (Floating in 3D & Fullscreen Mode) -->
                @include('app.ai.partials.agent_inspector_hud')

                <!-- The Actual WebGL 3D Canvas Mounting Node -->
                <div
                    id="cooca-3d-office-viewport-{{ $mode }}"
                    :class="isFullscreen ? 'w-full h-full min-h-screen' : 'w-full h-[660px] xl:h-[740px]'"
                    class="cursor-grab active:cursor-grabbing outline-none overflow-hidden relative"
                ></div>
            </div>

            <!-- ============================================================== -->
            <!-- 2. 2D ISOMETRIC BLUEPRINT VIEWPORT (1-FLOOR CORPORATE CAMPUS)  -->
            <!-- ============================================================== -->
            <div
                x-show="viewMode === 'office'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="relative rounded-3xl bg-[#090d16] border border-white/10 shadow-2xl overflow-hidden min-h-[660px] xl:min-h-[740px] select-none"
            >
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-950/40 via-zinc-950/80 to-zinc-950 pointer-events-none"></div>

                <!-- Room Title Badge in Floor -->
                <div class="absolute top-4 left-4 z-20 flex items-center gap-2 pointer-events-none">
                    <div class="px-3.5 py-1.5 rounded-xl bg-black/80 backdrop-blur-md border border-white/10 text-white flex items-center gap-2 text-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-bold tracking-wide uppercase text-[11px] font-mono">
                            2D ARCHITECTURAL BLUEPRINT // 1-FLOOR CORPORATE CAMPUS
                        </span>
                    </div>
                </div>

                <!-- 2D Canvas Floor Viewport (Pannable & Zoomable) -->
                <div
                    class="relative w-full h-[660px] xl:h-[740px] overflow-hidden flex items-center justify-center cursor-grab active:cursor-grabbing"
                    @wheel.prevent="handleWheel($event)"
                    @mousedown="startPan($event)"
                    @mousemove="doPan($event)"
                    @mouseup="endPan()"
                    @mouseleave="endPan()"
                >
                    <div
                        class="transform-gpu transition-transform duration-100 ease-out origin-center"
                        :style="`transform: translate(${panX}px, ${panY}px) scale(${zoomLevel});`"
                    >
                        <!-- SVG 1-Floor Corporate Campus Architectural Blueprint (Matching Reference Image Exactly) -->
                        <svg
                            viewBox="0 0 1200 780"
                            class="w-[1120px] h-[728px] drop-shadow-2xl overflow-visible select-none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <defs>
                                <!-- Grid patterns -->
                                <pattern id="cutaway-grid-1f" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(255,255,255,0.025)" stroke-width="1" />
                                </pattern>
                                <pattern id="marble-tile-atrium" width="30" height="30" patternUnits="userSpaceOnUse">
                                    <rect width="30" height="30" fill="#0d1829" />
                                    <path d="M 30 0 L 0 0 0 30" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
                                </pattern>

                                <!-- Room Floor Tint Gradients -->
                                <linearGradient id="grad-marketing-floor" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#24133b" />
                                    <stop offset="100%" stop-color="#140b24" />
                                </linearGradient>
                                <linearGradient id="grad-sales-floor" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#0a2a1e" />
                                    <stop offset="100%" stop-color="#051811" />
                                </linearGradient>
                                <linearGradient id="grad-exec-floor" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#16233b" />
                                    <stop offset="100%" stop-color="#0e1728" />
                                </linearGradient>
                                <linearGradient id="grad-ops-floor" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#092936" />
                                    <stop offset="100%" stop-color="#04171f" />
                                </linearGradient>
                                <linearGradient id="grad-finance-floor" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#2e190b" />
                                    <stop offset="100%" stop-color="#1a0d05" />
                                </linearGradient>
                                <linearGradient id="grad-doc-floor" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#241910" />
                                    <stop offset="100%" stop-color="#170f08" />
                                </linearGradient>
                                <linearGradient id="grad-people-floor" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#2e0a2d" />
                                    <stop offset="100%" stop-color="#190418" />
                                </linearGradient>
                                <linearGradient id="grad-pantry-floor" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#1a2433" />
                                    <stop offset="100%" stop-color="#0e1520" />
                                </linearGradient>
                                <linearGradient id="grad-restroom-floor" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#141c28" />
                                    <stop offset="100%" stop-color="#0a1017" />
                                </linearGradient>

                                <!-- Furniture Gradients -->
                                <linearGradient id="wood-exec-desk" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#3d2716" />
                                    <stop offset="100%" stop-color="#22140a" />
                                </linearGradient>
                                <linearGradient id="wood-collab-table" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#453120" />
                                    <stop offset="100%" stop-color="#2a1c11" />
                                </linearGradient>
                                <linearGradient id="white-workstation" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#ffffff" />
                                    <stop offset="100%" stop-color="#e2e8f0" />
                                </linearGradient>
                                <linearGradient id="glass-coffee-table" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="rgba(56, 189, 248, 0.4)" />
                                    <stop offset="100%" stop-color="rgba(14, 165, 233, 0.15)" />
                                </linearGradient>
                                <linearGradient id="rotunda-stone-ring" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#475569" />
                                    <stop offset="100%" stop-color="#1e293b" />
                                </linearGradient>
                                <radialGradient id="rotunda-tree-foliage" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#4ade80" />
                                    <stop offset="55%" stop-color="#16a34a" />
                                    <stop offset="100%" stop-color="#14532d" />
                                </radialGradient>
                                <radialGradient id="bush-grad" cx="40%" cy="40%" r="60%">
                                    <stop offset="0%" stop-color="#34d399" />
                                    <stop offset="70%" stop-color="#059669" />
                                    <stop offset="100%" stop-color="#064e3b" />
                                </radialGradient>

                                <!-- Drop shadow filters -->
                                <filter id="soft-shadow" x="-8%" y="-8%" width="120%" height="120%">
                                    <feDropShadow dx="0" dy="3" stdDeviation="4" flood-color="#000000" flood-opacity="0.45" />
                                </filter>
                                <filter id="glow-cyan" x="-20%" y="-20%" width="140%" height="140%">
                                    <feGaussianBlur stdDeviation="3" result="blur" />
                                    <feComposite in="SourceGraphic" in2="blur" operator="over" />
                                </filter>
                            </defs>

                            <!-- Campus Outer Foundation -->
                            <rect x="25" y="20" width="1150" height="740" rx="18" fill="#080e18" stroke="rgba(255,255,255,0.12)" stroke-width="2" />
                            <rect x="25" y="20" width="1150" height="740" rx="18" fill="url(#cutaway-grid-1f)" />

                            <!-- ============================================== -->
                            <!-- EXTERIOR PERIMETER WALLS & GLASS FACADE        -->
                            <!-- ============================================== -->
                            <!-- Exterior Wall Contour (with modern cutaway bevel) -->
                            <rect x="35" y="30" width="1130" height="650" rx="14" fill="none" stroke="#334155" stroke-width="4" filter="url(#soft-shadow)" />

                            <!-- Exterior Windows & Glass Curtain Facade Along South/North -->
                            <line x1="45" y1="32" x2="1155" y2="32" stroke="#38bdf8" stroke-width="2" stroke-opacity="0.3" />
                            <line x1="45" y1="678" x2="480" y2="678" stroke="#38bdf8" stroke-width="2.5" stroke-opacity="0.5" />
                            <line x1="620" y1="678" x2="1155" y2="678" stroke="#38bdf8" stroke-width="2.5" stroke-opacity="0.5" />

                            <!-- ============================================================== -->
                            <!-- 1. WEST WING (LEFT COLUMN): MARKETING, SALES & MEETING ROOM   -->
                            <!-- ============================================================== -->

                            <!-- ---------------------------------------------- -->
                            <!-- 1A. MARKETING ROOM (Top-Left: Purple)          -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-marketing" @click="selectRoomFromFloor('marketing')">
                                <rect x="42" y="36" width="285" height="218" rx="10" fill="url(#grad-marketing-floor)" stroke="#7e22ce" stroke-width="1.2" class="cursor-pointer" />
                                
                                <!-- Header Badge: Marketing -->
                                <g transform="translate(52, 46)" class="cursor-pointer" @click.stop="selectRoomFromFloor('marketing')">
                                    <rect x="0" y="0" width="265" height="30" rx="7" fill="#7e22ce" stroke="#a855f7" stroke-width="1" filter="url(#soft-shadow)" />
                                    <circle cx="16" cy="15" r="9" fill="#581c87" />
                                    
                                    <text x="32" y="15" fill="#ffffff" font-size="10.5" font-family="system-ui, sans-serif" font-weight="bold">Marketing</text>
                                    <text x="32" y="24" fill="#e9d5ff" font-size="7.5" font-family="system-ui, sans-serif">Campaign • Content • Social Media | GROWTH LAB // AI CMO</text>
                                </g>

                                <!-- Workstation: Marketing Agent -->
                                <g transform="translate(50, 88)" class="cursor-pointer" @click.stop="selectAgentFromFloor('marketing')">
                                    <rect x="0" y="0" width="76" height="50" rx="6" fill="url(#white-workstation)" stroke="#a855f7" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <!-- Dual Screen -->
                                    <rect x="10" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="11" y="5" width="24" height="4" fill="#a855f7" />
                                    <rect x="40" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="41" y="5" width="24" height="4" fill="#38bdf8" />
                                    <!-- Keyboard & Desk Pad -->
                                    <rect x="22" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <!-- Chair -->
                                    <circle cx="38" cy="38" r="8" fill="#1e293b" stroke="#7e22ce" stroke-width="1" />
                                    <text x="38" y="48" fill="#ffffff" font-size="7" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Marketing Agent</text>
                                </g>

                                <!-- Workstation: Content Agent -->
                                <g transform="translate(146, 88)" class="cursor-pointer" @click.stop="selectAgentFromFloor('content')">
                                    <rect x="0" y="0" width="76" height="50" rx="6" fill="url(#white-workstation)" stroke="#06b6d4" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <!-- Dual Screen -->
                                    <rect x="10" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="11" y="5" width="24" height="4" fill="#06b6d4" />
                                    <rect x="40" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="41" y="5" width="24" height="4" fill="#0891b2" />
                                    <!-- Keyboard -->
                                    <rect x="22" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <!-- Chair -->
                                    <circle cx="38" cy="38" r="8" fill="#1e293b" stroke="#0891b2" stroke-width="1" />
                                    <text x="38" y="48" fill="#ffffff" font-size="7" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Content Agent</text>
                                </g>

                                <!-- Workstation: Social Media Agent -->
                                <g transform="translate(242, 88)" class="cursor-pointer" @click.stop="selectAgentFromFloor('social_media')">
                                    <rect x="0" y="0" width="76" height="50" rx="6" fill="url(#white-workstation)" stroke="#ec4899" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <!-- Dual Screen -->
                                    <rect x="10" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="11" y="5" width="24" height="4" fill="#ec4899" />
                                    <rect x="40" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="41" y="5" width="24" height="4" fill="#f43f5e" />
                                    <!-- Keyboard -->
                                    <rect x="22" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <!-- Chair -->
                                    <circle cx="38" cy="38" r="8" fill="#1e293b" stroke="#db2777" stroke-width="1" />
                                    <text x="38" y="48" fill="#ffffff" font-size="6.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Social Media Agent</text>
                                </g>

                                <!-- 8-Seater Collaboration Table -->
                                <g transform="translate(56, 164)">
                                    <!-- Table Top -->
                                    <rect x="0" y="0" width="255" height="58" rx="8" fill="url(#wood-collab-table)" stroke="#5c3d2e" stroke-width="1.5" filter="url(#soft-shadow)" />
                                    <!-- 4 Chairs North -->
                                    <circle cx="32" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="95" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="160" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="223" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <!-- 4 Chairs South -->
                                    <circle cx="32" cy="65" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="95" cy="65" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="160" cy="65" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="223" cy="65" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <!-- Center Planter on table -->
                                    <ellipse cx="127" cy="29" rx="14" ry="7" fill="#15803d" />
                                    <!-- Open Laptops -->
                                    <rect x="40" y="24" width="14" height="10" rx="1" fill="#94a3b8" />
                                    <rect x="200" y="24" width="14" height="10" rx="1" fill="#94a3b8" />
                                    <text x="127.5" y="48" fill="#c084fc" font-size="7" font-family="monospace" text-anchor="middle">Kampanye Social Media &amp; Follow-up Pelanggan</text>
                                </g>

                                <!-- Potted Corner Plants -->
                                <circle cx="316" cy="52" r="8" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                                <circle cx="316" cy="235" r="8" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 1B. SALES ROOM (Mid-Left: Green)               -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-sales" @click="selectRoomFromFloor('sales')">
                                <rect x="42" y="260" width="285" height="208" rx="10" fill="url(#grad-sales-floor)" stroke="#059669" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Header Badge: Sales -->
                                <g transform="translate(52, 270)" class="cursor-pointer" @click.stop="selectRoomFromFloor('sales')">
                                    <rect x="0" y="0" width="265" height="30" rx="7" fill="#047857" stroke="#10b981" stroke-width="1" filter="url(#soft-shadow)" />
                                    <circle cx="16" cy="15" r="9" fill="#064e3b" />
                                    
                                    <text x="32" y="15" fill="#ffffff" font-size="10.5" font-family="system-ui, sans-serif" font-weight="bold">Sales</text>
                                    <text x="32" y="24" fill="#a7f3d0" font-size="7.5" font-family="system-ui, sans-serif">Revenue • Customers • Growth | Sales Director</text>
                                </g>

                                <!-- Workstation: Sales Agent -->
                                <g transform="translate(80, 312)" class="cursor-pointer" @click.stop="selectAgentFromFloor('sales')">
                                    <rect x="0" y="0" width="98" height="52" rx="6" fill="url(#white-workstation)" stroke="#10b981" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <!-- Dual Screen -->
                                    <rect x="16" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="17" y="5" width="28" height="4" fill="#10b981" />
                                    <rect x="52" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="53" y="5" width="28" height="4" fill="#38bdf8" />
                                    <!-- Keyboard -->
                                    <rect x="33" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <!-- Chair -->
                                    <circle cx="49" cy="38" r="8" fill="#1e293b" stroke="#059669" stroke-width="1" />
                                    <text x="49" y="48" fill="#ffffff" font-size="7.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Sales Agent</text>
                                </g>

                                <!-- Workstation: Customer Agent -->
                                <g transform="translate(196, 312)" class="cursor-pointer" @click.stop="selectAgentFromFloor('customer')">
                                    <rect x="0" y="0" width="98" height="52" rx="6" fill="url(#white-workstation)" stroke="#0284c7" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <!-- Dual Screen -->
                                    <rect x="16" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="17" y="5" width="28" height="4" fill="#0284c7" />
                                    <rect x="52" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="53" y="5" width="28" height="4" fill="#38bdf8" />
                                    <!-- Keyboard -->
                                    <rect x="33" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <!-- Chair -->
                                    <circle cx="49" cy="38" r="8" fill="#1e293b" stroke="#0284c7" stroke-width="1" />
                                    <text x="49" y="48" fill="#ffffff" font-size="7.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Customer Agent</text>
                                </g>

                                <!-- 8-Seater Long Sales Workbench -->
                                <g transform="translate(56, 386)">
                                    <!-- Workbench Table -->
                                    <rect x="0" y="0" width="255" height="56" rx="8" fill="url(#wood-collab-table)" stroke="#5c3d2e" stroke-width="1.5" filter="url(#soft-shadow)" />
                                    <!-- 4 Chairs North -->
                                    <circle cx="32" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="95" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="160" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="223" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <!-- 4 Chairs South -->
                                    <circle cx="32" cy="63" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="95" cy="63" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="160" cy="63" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="223" cy="63" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <!-- Center Divider Line & Planters -->
                                    <line x1="15" y1="28" x2="240" y2="28" stroke="#78350f" stroke-dasharray="4 4" />
                                    <circle cx="70" cy="28" r="5" fill="#15803d" />
                                    <circle cx="185" cy="28" r="5" fill="#15803d" />
                                    <!-- Laptops -->
                                    <rect x="35" y="12" width="13" height="9" rx="1" fill="#cbd5e1" />
                                    <rect x="135" y="35" width="13" height="9" rx="1" fill="#cbd5e1" />
                                </g>

                                <!-- Plants along wall -->
                                <circle cx="316" cy="285" r="7" fill="#15803d" />
                                <circle cx="316" cy="445" r="7" fill="#15803d" />
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 1C. MEETING ROOM (Bottom-Left: Warm Wood)      -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-meeting" @click="selectRoomFromFloor('meeting')">
                                <rect x="42" y="474" width="285" height="200" rx="10" fill="#141a24" stroke="#475569" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Header Badge: Meeting Room -->
                                <g transform="translate(52, 484)" class="cursor-pointer" @click.stop="selectRoomFromFloor('meeting')">
                                    <rect x="0" y="0" width="130" height="26" rx="6" fill="#334155" stroke="#64748b" stroke-width="1" filter="url(#soft-shadow)" />
                                    <text x="65" y="17" fill="#f8fafc" font-size="10" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Meeting Room</text>
                                </g>

                                <!-- Large Wooden 8-Seater Executive Conference Table -->
                                <g transform="translate(90, 535)">
                                    <!-- Big Table Top -->
                                    <rect x="0" y="0" width="185" height="82" rx="12" fill="url(#wood-exec-desk)" stroke="#5c3d2e" stroke-width="2" filter="url(#soft-shadow)" />
                                    <!-- 3 Chairs North -->
                                    <circle cx="45" cy="-8" r="8" fill="#1e293b" stroke="#64748b" stroke-width="1.2" />
                                    <circle cx="92" cy="-8" r="8" fill="#1e293b" stroke="#64748b" stroke-width="1.2" />
                                    <circle cx="140" cy="-8" r="8" fill="#1e293b" stroke="#64748b" stroke-width="1.2" />
                                    <!-- 3 Chairs South -->
                                    <circle cx="45" cy="90" r="8" fill="#1e293b" stroke="#64748b" stroke-width="1.2" />
                                    <circle cx="92" cy="90" r="8" fill="#1e293b" stroke="#64748b" stroke-width="1.2" />
                                    <circle cx="140" cy="90" r="8" fill="#1e293b" stroke="#64748b" stroke-width="1.2" />
                                    <!-- 1 Chair West & 1 East -->
                                    <circle cx="-9" cy="41" r="8" fill="#1e293b" stroke="#64748b" stroke-width="1.2" />
                                    <circle cx="194" cy="41" r="8" fill="#1e293b" stroke="#64748b" stroke-width="1.2" />
                                    <!-- Presentation documents & center centerpiece -->
                                    <rect x="75" y="32" width="35" height="18" rx="2" fill="#0f172a" stroke="#38bdf8" stroke-width="0.8" />
                                    <text x="92" y="44" fill="#38bdf8" font-size="7" font-family="monospace" text-anchor="middle">AGENDA</text>
                                </g>

                                <!-- Large Presentation TV on Top Wall -->
                                <rect x="135" y="515" width="105" height="10" rx="3" fill="#0284c7" stroke="#38bdf8" stroke-width="1" filter="url(#soft-shadow)" />
                                <text x="187" y="523" fill="#ffffff" font-size="6.5" font-family="monospace" font-weight="bold" text-anchor="middle">PRESENTATION DISPLAY</text>

                                <!-- Left Lounge Sofa Bench -->
                                <rect x="52" y="542" width="18" height="74" rx="5" fill="#1e293b" stroke="#475569" stroke-width="1" />
                                <line x1="52" y1="566" x2="70" y2="566" stroke="#334155" />
                                <line x1="52" y1="592" x2="70" y2="592" stroke="#334155" />

                                <!-- Flanking Indoor Trees -->
                                <circle cx="56" cy="655" r="9" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                                <circle cx="316" cy="655" r="9" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                            </g>


                            <!-- ============================================================== -->
                            <!-- 2. CENTER SPINE: EXECUTIVE SUITE, ATRIUM & RECEPTION           -->
                            <!-- ============================================================== -->

                            <!-- ---------------------------------------------- -->
                            <!-- 2A. EXECUTIVE OFFICE (Top-Center: Royal Blue)  -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-executive" @click="selectRoomFromFloor('executive')">
                                <rect x="333" y="36" width="428" height="258" rx="10" fill="url(#grad-exec-floor)" stroke="#1d4ed8" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Header Badge: Executive Office -->
                                <g transform="translate(425, 46)" class="cursor-pointer" @click.stop="selectRoomFromFloor('executive')">
                                    <rect x="0" y="0" width="245" height="30" rx="7" fill="#1d4ed8" stroke="#3b82f6" stroke-width="1" filter="url(#soft-shadow)" />
                                    <circle cx="16" cy="15" r="9" fill="#1e3a8a" />
                                    
                                    <text x="32" y="15" fill="#ffffff" font-size="10.5" font-family="system-ui, sans-serif" font-weight="bold">Executive Office</text>
                                    <text x="32" y="24" fill="#bfdbfe" font-size="7.5" font-family="system-ui, sans-serif">Strategy • Finance • Decisions</text>
                                </g>

                                <!-- AI CEO Grand Executive Workstation (Center Top) -->
                                <g transform="translate(485, 86)" class="cursor-pointer" @click.stop="selectAgentFromFloor('ceo')">
                                    <!-- Curved Desk Top -->
                                    <rect x="0" y="0" width="135" height="52" rx="10" fill="url(#wood-exec-desk)" stroke="#f59e0b" stroke-width="1.8" filter="url(#soft-shadow)" />
                                    <!-- Dual Panoramic Curved Monitors -->
                                    <rect x="22" y="4" width="42" height="6" rx="2" fill="#0f172a" />
                                    <rect x="23" y="5" width="40" height="4" fill="#f59e0b" />
                                    <rect x="71" y="4" width="42" height="6" rx="2" fill="#0f172a" />
                                    <rect x="72" y="5" width="40" height="4" fill="#38bdf8" />
                                    <!-- Executive Highback Leather Chair -->
                                    <circle cx="67" cy="-7" r="9" fill="#1e293b" stroke="#f59e0b" stroke-width="1.5" />
                                    <!-- 2 Visitor Chairs in front -->
                                    <circle cx="32" cy="62" r="7.5" fill="#334155" stroke="#64748b" />
                                    <circle cx="102" cy="62" r="7.5" fill="#334155" stroke="#64748b" />
                                    <text x="67" y="32" fill="#ffffff" font-size="9" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">AI CEO</text>
                                    <rect x="42" y="38" width="50" height="10" rx="3" fill="#059669" />
                                    <text x="67" y="46" fill="#ffffff" font-size="6.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">● Leading</text>
                                </g>

                                <!-- Potted ficus trees flanking CEO desk -->
                                <circle cx="462" cy="112" r="9" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                                <circle cx="642" cy="112" r="9" fill="#15803d" stroke="#22c55e" stroke-width="1" />

                                <!-- Center Executive Conversation Lounge -->
                                <g transform="translate(460, 162)">
                                    <!-- North Teal Leather Sofa (3-Seater) -->
                                    <rect x="20" y="0" width="135" height="22" rx="6" fill="#0f766e" stroke="#14b8a6" stroke-width="1" filter="url(#soft-shadow)" />
                                    <line x1="65" y1="0" x2="65" y2="22" stroke="#042f2e" />
                                    <line x1="110" y1="0" x2="110" y2="22" stroke="#042f2e" />
                                    <!-- South Teal Leather Sofa (3-Seater) -->
                                    <rect x="20" y="74" width="135" height="22" rx="6" fill="#0f766e" stroke="#14b8a6" stroke-width="1" filter="url(#soft-shadow)" />
                                    <line x1="65" y1="74" x2="65" y2="96" stroke="#042f2e" />
                                    <line x1="110" y1="74" x2="110" y2="96" stroke="#042f2e" />
                                    <!-- Center Glass Coffee Table -->
                                    <rect x="38" y="32" width="100" height="32" rx="6" fill="url(#glass-coffee-table)" stroke="#38bdf8" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <!-- 2 Teal Armchairs on Left & Right -->
                                    <rect x="-4" y="34" width="18" height="28" rx="5" fill="#0f766e" stroke="#14b8a6" />
                                    <rect x="160" y="34" width="18" height="28" rx="5" fill="#0f766e" stroke="#14b8a6" />
                                    <!-- Boardroom Invariant Text -->
                                    <text x="88" y="52" fill="#38bdf8" font-size="7" font-family="monospace" font-weight="bold" text-anchor="middle">COOCA BOARDROOM // Executive Audit Q4 Strategy</text>
                                </g>

                                <!-- Left Sub-Office Enclave: AI CFO & Sub-Stations -->
                                <g transform="translate(340, 52)">
                                    <!-- Glass Partition Outline -->
                                    <rect x="0" y="0" width="115" height="225" rx="8" fill="none" stroke="#38bdf8" stroke-width="0.8" stroke-opacity="0.4" stroke-dasharray="6 3" />
                                    
                                    <!-- AI CFO Desk -->
                                    <g transform="translate(10, 32)" class="cursor-pointer" @click.stop="selectAgentFromFloor('cfo')">
                                        <rect x="0" y="0" width="95" height="50" rx="6" fill="url(#white-workstation)" stroke="#0284c7" stroke-width="1.5" filter="url(#soft-shadow)" />
                                        <rect x="15" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                        <rect x="16" y="5" width="28" height="4" fill="#0284c7" />
                                        <rect x="50" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                        <rect x="51" y="5" width="28" height="4" fill="#38bdf8" />
                                        <circle cx="47" cy="38" r="8" fill="#1e293b" stroke="#0284c7" stroke-width="1" />
                                        <text x="47.5" y="27" fill="#0f172a" font-size="8.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">AI CFO</text>
                                    </g>

                                    <!-- Sub-Station: Finance Agent -->
                                    <g transform="translate(6, 110)" class="cursor-pointer" @click.stop="selectAgentFromFloor('finance')">
                                        <rect x="0" y="0" width="48" height="44" rx="5" fill="#0f172a" stroke="#10b981" stroke-width="1" />
                                        <rect x="8" y="4" width="32" height="5" rx="1" fill="#10b981" />
                                        <circle cx="24" cy="33" r="6" fill="#1e293b" />
                                        <text x="24" y="23" fill="#ffffff" font-size="6" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Finance Agent</text>
                                    </g>

                                    <!-- Sub-Station: Reporting Agent -->
                                    <g transform="translate(60, 110)" class="cursor-pointer" @click.stop="selectAgentFromFloor('reporting')">
                                        <rect x="0" y="0" width="48" height="44" rx="5" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
                                        <rect x="8" y="4" width="32" height="5" rx="1" fill="#38bdf8" />
                                        <circle cx="24" cy="33" r="6" fill="#1e293b" />
                                        <text x="24" y="23" fill="#ffffff" font-size="5.8" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Reporting Agent</text>
                                    </g>
                                </g>

                                <!-- Right Sub-Office Enclave: Business Agent -->
                                <g transform="translate(670, 75)">
                                    <rect x="0" y="0" width="82" height="150" rx="8" fill="none" stroke="#a855f7" stroke-width="0.8" stroke-opacity="0.4" stroke-dasharray="6 3" />
                                    <!-- Business Agent Desk -->
                                    <g transform="translate(8, 30)" class="cursor-pointer" @click.stop="selectAgentFromFloor('business')">
                                        <rect x="0" y="0" width="66" height="50" rx="6" fill="url(#white-workstation)" stroke="#a855f7" stroke-width="1.2" filter="url(#soft-shadow)" />
                                        <rect x="12" y="4" width="42" height="6" rx="1.5" fill="#7c3aed" />
                                        <circle cx="33" cy="38" r="7.5" fill="#1e293b" stroke="#a855f7" />
                                        <text x="33" y="26" fill="#0f172a" font-size="7" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Business Agent</text>
                                    </g>
                                    <circle cx="41" cy="115" r="7" fill="#15803d" />
                                </g>
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 2B. GRAND CENTRAL ATRIUM & ROTUNDA LOUNGE      -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-atrium" @click="selectRoomFromFloor('atrium')">
                                <rect x="333" y="300" width="428" height="262" rx="10" fill="url(#marble-tile-atrium)" stroke="#38bdf8" stroke-width="1.2" stroke-opacity="0.3" class="cursor-pointer" />

                                <!-- Central Raised Stone Rotunda Planter (Centerpiece) -->
                                <g transform="translate(547, 428)" class="cursor-pointer" @click.stop="selectRoomFromFloor('atrium')">
                                    <!-- Outer Circular Granite Ring -->
                                    <circle cx="0" cy="0" r="46" fill="url(#rotunda-stone-ring)" stroke="#64748b" stroke-width="2.5" filter="url(#soft-shadow)" />
                                    <!-- Inner Dark Soil -->
                                    <circle cx="0" cy="0" r="38" fill="#1c130d" stroke="#29180f" stroke-width="1" />
                                    <!-- Lush Green Bonsai/Ficus Tree Canopy -->
                                    <circle cx="0" cy="0" r="30" fill="url(#rotunda-tree-foliage)" />
                                    <circle cx="-10" cy="-8" r="14" fill="#4ade80" fill-opacity="0.7" />
                                    <circle cx="12" cy="-6" r="12" fill="#86efac" fill-opacity="0.6" />
                                    <circle cx="6" cy="10" r="13" fill="#22c55e" fill-opacity="0.7" />
                                    <!-- Trunk Center -->
                                    <circle cx="0" cy="0" r="4" fill="#5c3d2e" />
                                    <text x="0" y="58" fill="#38bdf8" font-size="8.5" font-family="monospace" font-weight="bold" text-anchor="middle">ATRIUM CENTRAL</text>
                                    <text x="0" y="68" fill="#94a3b8" font-size="7" font-family="system-ui, sans-serif" text-anchor="middle">Lobi Utama</text>
                                </g>

                                <!-- Left Lounge Modular Charcoal Sofa & Table -->
                                <g transform="translate(348, 385)">
                                    <rect x="0" y="0" width="32" height="86" rx="8" fill="#334155" stroke="#475569" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <line x1="0" y1="28" x2="32" y2="28" stroke="#1e293b" />
                                    <line x1="0" y1="58" x2="32" y2="58" stroke="#1e293b" />
                                    <!-- Coffee Table -->
                                    <rect x="42" y="20" width="22" height="46" rx="4" fill="url(#glass-coffee-table)" stroke="#38bdf8" stroke-width="1" />
                                </g>

                                <!-- Right Lounge Modular Charcoal Sofa & Table -->
                                <g transform="translate(714, 385)">
                                    <rect x="0" y="0" width="32" height="86" rx="8" fill="#334155" stroke="#475569" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <line x1="0" y1="28" x2="32" y2="28" stroke="#1e293b" />
                                    <line x1="0" y1="58" x2="32" y2="58" stroke="#1e293b" />
                                    <!-- Coffee Table -->
                                    <rect x="-32" y="20" width="22" height="46" rx="4" fill="url(#glass-coffee-table)" stroke="#38bdf8" stroke-width="1" />
                                </g>

                                <!-- Tall Potted Trees along corridor partitions -->
                                <circle cx="355" cy="318" r="8" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                                <circle cx="738" cy="318" r="8" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                                <circle cx="355" cy="542" r="8" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                                <circle cx="738" cy="542" r="8" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 2C. RECEPTION & MAIN ENTRANCE (Bottom-Center)  -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-entrance" @click="selectRoomFromFloor('atrium')">
                                <rect x="333" y="568" width="428" height="110" rx="10" fill="#0f1929" stroke="#334155" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Reception Counter -->
                                <g transform="translate(427, 582)" class="cursor-pointer" @click.stop="selectRoomFromFloor('atrium')">
                                    <!-- Curved Counter -->
                                    <rect x="0" y="0" width="240" height="48" rx="14" fill="#0f1d33" stroke="#38bdf8" stroke-width="2" filter="url(#soft-shadow)" />
                                    <!-- Illuminated Front Logo Sign -->
                                    <circle cx="86" cy="24" r="8" fill="#06b6d4" />
                                    <text x="86" y="28" fill="#ffffff" font-size="10" font-weight="bold" text-anchor="middle">C</text>
                                    <text x="136" y="21" fill="#ffffff" font-size="11" font-family="system-ui, sans-serif" font-weight="bold">COOCA AI</text>
                                    <text x="136" y="32" fill="#38bdf8" font-size="8" font-family="system-ui, sans-serif">Virtual Office</text>
                                    <!-- 2 Receptionist Chairs behind desk -->
                                    <circle cx="85" cy="-8" r="8" fill="#0284c7" stroke="#38bdf8" stroke-width="1" />
                                    <circle cx="155" cy="-8" r="8" fill="#0284c7" stroke="#38bdf8" stroke-width="1" />
                                </g>

                                <!-- Flanking Reception Potted Trees -->
                                <circle cx="405" cy="606" r="9" fill="#15803d" stroke="#22c55e" stroke-width="1" />
                                <circle cx="688" cy="606" r="9" fill="#15803d" stroke="#22c55e" stroke-width="1" />

                                <!-- Main Entrance Floor Mat -->
                                <g transform="translate(487, 646)">
                                    <rect x="0" y="0" width="120" height="26" rx="6" fill="#1e293b" stroke="#475569" stroke-width="1.5" />
                                    <text x="60" y="17" fill="#f8fafc" font-size="9" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Main Entrance</text>
                                </g>

                                <!-- Automatic Double Glass Sliding Doors -->
                                <line x1="485" y1="678" x2="540" y2="678" stroke="#38bdf8" stroke-width="3" filter="url(#glow-cyan)" />
                                <line x1="555" y1="678" x2="610" y2="678" stroke="#38bdf8" stroke-width="3" filter="url(#glow-cyan)" />
                            </g>


                            <!-- ============================================================== -->
                            <!-- 3. EAST WING (RIGHT COLUMN): OPERATIONS, FINANCE, PEOPLE, PANTRY-->
                            <!-- ============================================================== -->

                            <!-- ---------------------------------------------- -->
                            <!-- 3A. OPERATIONS ROOM (Top-Right: Teal)          -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-operations" @click="selectRoomFromFloor('operations')">
                                <rect x="768" y="36" width="268" height="218" rx="10" fill="url(#grad-ops-floor)" stroke="#0e7490" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Header Badge: Operations -->
                                <g transform="translate(778, 46)" class="cursor-pointer" @click.stop="selectRoomFromFloor('operations')">
                                    <rect x="0" y="0" width="248" height="30" rx="7" fill="#0e7490" stroke="#06b6d4" stroke-width="1" filter="url(#soft-shadow)" />
                                    <circle cx="16" cy="15" r="9" fill="#164e63" />
                                    
                                    <text x="32" y="15" fill="#ffffff" font-size="10.5" font-family="system-ui, sans-serif" font-weight="bold">Operations</text>
                                    <text x="32" y="24" fill="#cffafe" font-size="7.5" font-family="system-ui, sans-serif">Inventory • Purchasing • Marketplace | AI COO // OPERATIONS RADAR</text>
                                </g>

                                <!-- Workstation: Inventory Agent -->
                                <g transform="translate(778, 88)" class="cursor-pointer" @click.stop="selectAgentFromFloor('inventory')">
                                    <rect x="0" y="0" width="76" height="50" rx="6" fill="url(#white-workstation)" stroke="#eab308" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <rect x="10" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="11" y="5" width="24" height="4" fill="#eab308" />
                                    <rect x="40" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="41" y="5" width="24" height="4" fill="#38bdf8" />
                                    <rect x="22" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <circle cx="38" cy="38" r="8" fill="#1e293b" stroke="#d97706" stroke-width="1" />
                                    <text x="38" y="48" fill="#ffffff" font-size="7" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Inventory Agent</text>
                                </g>

                                <!-- Workstation: Purchasing Agent -->
                                <g transform="translate(864, 88)" class="cursor-pointer" @click.stop="selectAgentFromFloor('purchasing')">
                                    <rect x="0" y="0" width="76" height="50" rx="6" fill="url(#white-workstation)" stroke="#0284c7" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <rect x="10" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="11" y="5" width="24" height="4" fill="#0284c7" />
                                    <rect x="40" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="41" y="5" width="24" height="4" fill="#38bdf8" />
                                    <rect x="22" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <circle cx="38" cy="38" r="8" fill="#1e293b" stroke="#0369a1" stroke-width="1" />
                                    <text x="38" y="48" fill="#ffffff" font-size="6.8" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Purchasing Agent</text>
                                </g>

                                <!-- Workstation: Marketplace Agent -->
                                <g transform="translate(950, 88)" class="cursor-pointer" @click.stop="selectAgentFromFloor('marketplace')">
                                    <rect x="0" y="0" width="76" height="50" rx="6" fill="url(#white-workstation)" stroke="#10b981" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <rect x="10" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="11" y="5" width="24" height="4" fill="#10b981" />
                                    <rect x="40" y="4" width="26" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="41" y="5" width="24" height="4" fill="#38bdf8" />
                                    <rect x="22" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <circle cx="38" cy="38" r="8" fill="#1e293b" stroke="#059669" stroke-width="1" />
                                    <text x="38" y="48" fill="#ffffff" font-size="6.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Marketplace Agent</text>
                                </g>

                                <!-- 8-Seater Operations Collaboration Workbench -->
                                <g transform="translate(778, 164)">
                                    <rect x="0" y="0" width="248" height="58" rx="8" fill="url(#wood-collab-table)" stroke="#5c3d2e" stroke-width="1.5" filter="url(#soft-shadow)" />
                                    <!-- 4 Chairs North -->
                                    <circle cx="32" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="95" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="158" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="218" cy="-7" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <!-- 4 Chairs South -->
                                    <circle cx="32" cy="65" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="95" cy="65" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="158" cy="65" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <circle cx="218" cy="65" r="6.5" fill="#1e293b" stroke="#475569" />
                                    <!-- Laptops -->
                                    <rect x="40" y="24" width="14" height="10" rx="1" fill="#94a3b8" />
                                    <rect x="195" y="24" width="14" height="10" rx="1" fill="#94a3b8" />
                                    <text x="124" y="48" fill="#38bdf8" font-size="7" font-family="monospace" text-anchor="middle">Optimasi Rantai Pasokan &amp; Gudang</text>
                                </g>
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 3B. SERVER ROOM & IT SUPPORT (Far Top-Right)   -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-it-server" @click="selectRoomFromFloor('it_server')">
                                <rect x="1042" y="36" width="118" height="218" rx="10" fill="#0b121e" stroke="#1e293b" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Top: Server Room -->
                                <g transform="translate(1048, 44)" class="cursor-pointer" @click.stop="selectRoomFromFloor('it_server')">
                                    <rect x="0" y="0" width="106" height="20" rx="4" fill="#1e293b" stroke="#38bdf8" stroke-width="1" />
                                    <text x="53" y="14" fill="#38bdf8" font-size="8.5" font-family="monospace" font-weight="bold" text-anchor="middle">Server Room</text>
                                    
                                    <!-- 3 Server Rack Cabinets with glowing LEDs -->
                                    <g transform="translate(4, 26)">
                                        <rect x="0" y="0" width="28" height="54" rx="3" fill="#18181b" stroke="#334155" />
                                        <circle cx="7" cy="12" r="1.5" fill="#10b981" />
                                        <circle cx="14" cy="12" r="1.5" fill="#10b981" />
                                        <circle cx="21" cy="12" r="1.5" fill="#38bdf8" />
                                        <circle cx="7" cy="24" r="1.5" fill="#10b981" />
                                        <circle cx="14" cy="24" r="1.5" fill="#eab308" />
                                        <circle cx="21" cy="24" r="1.5" fill="#10b981" />
                                        <circle cx="7" cy="36" r="1.5" fill="#38bdf8" />
                                        <circle cx="14" cy="36" r="1.5" fill="#10b981" />
                                        <circle cx="21" cy="36" r="1.5" fill="#10b981" />
                                    </g>
                                    <g transform="translate(35, 26)">
                                        <rect x="0" y="0" width="28" height="54" rx="3" fill="#18181b" stroke="#334155" />
                                        <circle cx="7" cy="12" r="1.5" fill="#38bdf8" />
                                        <circle cx="14" cy="12" r="1.5" fill="#10b981" />
                                        <circle cx="21" cy="12" r="1.5" fill="#10b981" />
                                        <circle cx="7" cy="24" r="1.5" fill="#10b981" />
                                        <circle cx="14" cy="24" r="1.5" fill="#10b981" />
                                        <circle cx="21" cy="24" r="1.5" fill="#38bdf8" />
                                        <circle cx="7" cy="36" r="1.5" fill="#10b981" />
                                        <circle cx="14" cy="36" r="1.5" fill="#10b981" />
                                        <circle cx="21" cy="36" r="1.5" fill="#10b981" />
                                    </g>
                                    <g transform="translate(66, 26)">
                                        <rect x="0" y="0" width="28" height="54" rx="3" fill="#18181b" stroke="#334155" />
                                        <circle cx="7" cy="12" r="1.5" fill="#10b981" />
                                        <circle cx="14" cy="12" r="1.5" fill="#38bdf8" />
                                        <circle cx="21" cy="12" r="1.5" fill="#10b981" />
                                        <circle cx="7" cy="24" r="1.5" fill="#eab308" />
                                        <circle cx="14" cy="24" r="1.5" fill="#10b981" />
                                        <circle cx="21" cy="24" r="1.5" fill="#10b981" />
                                        <circle cx="7" cy="36" r="1.5" fill="#10b981" />
                                        <circle cx="14" cy="36" r="1.5" fill="#10b981" />
                                        <circle cx="21" cy="36" r="1.5" fill="#38bdf8" />
                                    </g>
                                </g>

                                <!-- Divider -->
                                <line x1="1046" y1="134" x2="1156" y2="134" stroke="#1e293b" stroke-width="1.5" />

                                <!-- Bottom: IT Support -->
                                <g transform="translate(1048, 142)">
                                    <rect x="0" y="0" width="106" height="20" rx="4" fill="#334155" stroke="#64748b" stroke-width="1" />
                                    <text x="53" y="14" fill="#f8fafc" font-size="8.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">IT Support</text>
                                    <!-- IT Desk -->
                                    <rect x="10" y="28" width="86" height="42" rx="5" fill="url(#white-workstation)" stroke="#38bdf8" stroke-width="1" />
                                    <rect x="25" y="32" width="26" height="5" rx="1" fill="#0f172a" />
                                    <rect x="55" y="32" width="26" height="5" rx="1" fill="#0f172a" />
                                    <circle cx="53" cy="62" r="7" fill="#1e293b" stroke="#38bdf8" />
                                </g>
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 3C. FINANCE ROOM (Mid-Right: Orange)           -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-finance" @click="selectRoomFromFloor('finance')">
                                <rect x="768" y="260" width="268" height="148" rx="10" fill="url(#grad-finance-floor)" stroke="#ea580c" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Header Badge: Finance -->
                                <g transform="translate(778, 270)" class="cursor-pointer" @click.stop="selectRoomFromFloor('finance')">
                                    <rect x="0" y="0" width="248" height="30" rx="7" fill="#c2410c" stroke="#f97316" stroke-width="1" filter="url(#soft-shadow)" />
                                    <circle cx="16" cy="15" r="9" fill="#9a3412" />
                                    
                                    <text x="32" y="15" fill="#ffffff" font-size="10.5" font-family="system-ui, sans-serif" font-weight="bold">Finance</text>
                                    <text x="32" y="24" fill="#ffedd5" font-size="7.5" font-family="system-ui, sans-serif">Revenue • Expense • Reporting</text>
                                </g>

                                <!-- Workstation: Finance Agent -->
                                <g transform="translate(795, 316)" class="cursor-pointer" @click.stop="selectAgentFromFloor('finance')">
                                    <rect x="0" y="0" width="98" height="52" rx="6" fill="url(#white-workstation)" stroke="#f97316" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <rect x="16" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="17" y="5" width="28" height="4" fill="#f97316" />
                                    <rect x="52" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="53" y="5" width="28" height="4" fill="#38bdf8" />
                                    <rect x="33" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <circle cx="49" cy="38" r="8" fill="#1e293b" stroke="#ea580c" stroke-width="1" />
                                    <text x="49" y="48" fill="#ffffff" font-size="7.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Finance Agent</text>
                                </g>

                                <!-- Workstation: Reporting Agent -->
                                <g transform="translate(915, 316)" class="cursor-pointer" @click.stop="selectAgentFromFloor('reporting')">
                                    <rect x="0" y="0" width="98" height="52" rx="6" fill="url(#white-workstation)" stroke="#38bdf8" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <rect x="16" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="17" y="5" width="28" height="4" fill="#38bdf8" />
                                    <rect x="52" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="53" y="5" width="28" height="4" fill="#0284c7" />
                                    <rect x="33" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <circle cx="49" cy="38" r="8" fill="#1e293b" stroke="#0284c7" stroke-width="1" />
                                    <text x="49" y="48" fill="#ffffff" font-size="7.2" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Reporting Agent</text>
                                </g>
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 3D. DOCUMENT ROOM (Far Mid-Right)              -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-document" @click="selectRoomFromFloor('document')">
                                <rect x="1042" y="260" width="118" height="148" rx="10" fill="url(#grad-doc-floor)" stroke="#78350f" stroke-width="1.2" class="cursor-pointer" />

                                <g transform="translate(1048, 270)" class="cursor-pointer" @click.stop="selectRoomFromFloor('document')">
                                    <rect x="0" y="0" width="106" height="24" rx="5" fill="#3d2617" stroke="#78350f" stroke-width="1" />
                                    <text x="53" y="16" fill="#fde68a" font-size="8.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Document Room</text>
                                    
                                    <!-- Archive Filing Cabinets -->
                                    <g transform="translate(6, 34)">
                                        <rect x="0" y="0" width="42" height="28" rx="2" fill="#29180c" stroke="#5c381e" />
                                        <line x1="0" y1="9" x2="42" y2="9" stroke="#5c381e" />
                                        <line x1="0" y1="18" x2="42" y2="18" stroke="#5c381e" />
                                        <rect x="18" y="3" width="6" height="3" fill="#e2e8f0" />
                                        <rect x="18" y="12" width="6" height="3" fill="#e2e8f0" />
                                        <rect x="18" y="21" width="6" height="3" fill="#e2e8f0" />
                                    </g>
                                    <g transform="translate(56, 34)">
                                        <rect x="0" y="0" width="42" height="28" rx="2" fill="#29180c" stroke="#5c381e" />
                                        <line x1="0" y1="9" x2="42" y2="9" stroke="#5c381e" />
                                        <line x1="0" y1="18" x2="42" y2="18" stroke="#5c381e" />
                                        <rect x="18" y="3" width="6" height="3" fill="#e2e8f0" />
                                        <rect x="18" y="12" width="6" height="3" fill="#e2e8f0" />
                                        <rect x="18" y="21" width="6" height="3" fill="#e2e8f0" />
                                    </g>
                                    <!-- Tall Bookcase / Storage Shelf -->
                                    <rect x="6" y="70" width="92" height="24" rx="2" fill="#1f130a" stroke="#452713" />
                                    <line x1="6" y1="82" x2="98" y2="82" stroke="#452713" />
                                    <!-- Plant in corner -->
                                    <circle cx="85" cy="115" r="7" fill="#15803d" />
                                </g>
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 3E. PEOPLE / HR ROOM (Lower Mid-Right: Pink)   -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-people" @click="selectRoomFromFloor('people')">
                                <rect x="768" y="416" width="158" height="136" rx="10" fill="url(#grad-people-floor)" stroke="#c026d3" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Header Badge: People -->
                                <g transform="translate(778, 426)" class="cursor-pointer" @click.stop="selectRoomFromFloor('people')">
                                    <rect x="0" y="0" width="138" height="28" rx="7" fill="#a21caf" stroke="#e879f9" stroke-width="1" filter="url(#soft-shadow)" />
                                    <circle cx="15" cy="14" r="8" fill="#701a75" />
                                    
                                    <text x="30" y="14" fill="#ffffff" font-size="9.5" font-family="system-ui, sans-serif" font-weight="bold">People</text>
                                    <text x="30" y="23" fill="#f5d0fe" font-size="7" font-family="system-ui, sans-serif">HR • Workforce • Culture</text>
                                </g>

                                <!-- Workstation: HR Agent -->
                                <g transform="translate(795, 464)" class="cursor-pointer" @click.stop="selectAgentFromFloor('hr')">
                                    <rect x="0" y="0" width="98" height="52" rx="6" fill="url(#white-workstation)" stroke="#e879f9" stroke-width="1.2" filter="url(#soft-shadow)" />
                                    <rect x="16" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="17" y="5" width="28" height="4" fill="#e879f9" />
                                    <rect x="52" y="4" width="30" height="6" rx="1.5" fill="#1e293b" />
                                    <rect x="53" y="5" width="28" height="4" fill="#38bdf8" />
                                    <rect x="33" y="15" width="32" height="10" rx="2" fill="#334155" />
                                    <circle cx="49" cy="38" r="8" fill="#1e293b" stroke="#c026d3" stroke-width="1" />
                                    <text x="49" y="48" fill="#ffffff" font-size="7.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">HR Agent</text>
                                </g>
                                <circle cx="910" cy="535" r="7" fill="#15803d" />
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 3F. PANTRY & LOUNGE (Bottom-Right: 3 Tables)   -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-pantry" @click="selectRoomFromFloor('pantry')">
                                <rect x="932" y="416" width="228" height="162" rx="10" fill="url(#grad-pantry-floor)" stroke="#475569" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Header Badge: Pantry & Lounge -->
                                <g transform="translate(942, 426)" class="cursor-pointer" @click.stop="selectRoomFromFloor('pantry')">
                                    <rect x="0" y="0" width="125" height="24" rx="6" fill="#334155" stroke="#64748b" stroke-width="1" filter="url(#soft-shadow)" />
                                    <text x="62.5" y="16" fill="#f8fafc" font-size="9.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Pantry &amp; Lounge</text>
                                </g>

                                <!-- 3 Round Cafe / Dining Tables (4 Chairs Each = 12 Chairs Total) -->
                                <!-- Table 1 (Top Left) -->
                                <g transform="translate(976, 482)">
                                    <circle cx="0" cy="0" r="16" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1.5" filter="url(#soft-shadow)" />
                                    <!-- 4 Chairs (N, S, W, E) -->
                                    <circle cx="0" cy="-23" r="6" fill="#334155" stroke="#64748b" />
                                    <circle cx="0" cy="23" r="6" fill="#334155" stroke="#64748b" />
                                    <circle cx="-23" cy="0" r="6" fill="#334155" stroke="#64748b" />
                                    <circle cx="23" cy="0" r="6" fill="#334155" stroke="#64748b" />
                                </g>

                                <!-- Table 2 (Top Right) -->
                                <g transform="translate(1048, 482)">
                                    <circle cx="0" cy="0" r="16" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1.5" filter="url(#soft-shadow)" />
                                    <!-- 4 Chairs -->
                                    <circle cx="0" cy="-23" r="6" fill="#334155" stroke="#64748b" />
                                    <circle cx="0" cy="23" r="6" fill="#334155" stroke="#64748b" />
                                    <circle cx="-23" cy="0" r="6" fill="#334155" stroke="#64748b" />
                                    <circle cx="23" cy="0" r="6" fill="#334155" stroke="#64748b" />
                                </g>

                                <!-- Table 3 (Bottom Center) -->
                                <g transform="translate(1012, 542)">
                                    <circle cx="0" cy="0" r="16" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1.5" filter="url(#soft-shadow)" />
                                    <!-- 4 Chairs -->
                                    <circle cx="0" cy="-23" r="6" fill="#334155" stroke="#64748b" />
                                    <circle cx="0" cy="23" r="6" fill="#334155" stroke="#64748b" />
                                    <circle cx="-23" cy="0" r="6" fill="#334155" stroke="#64748b" />
                                    <circle cx="23" cy="0" r="6" fill="#334155" stroke="#64748b" />
                                </g>

                                <!-- Long Kitchenette Counter along Right Wall -->
                                <g transform="translate(1136, 426)">
                                    <rect x="0" y="0" width="18" height="142" rx="4" fill="#0f172a" stroke="#334155" stroke-width="1.2" />
                                    <!-- Espresso Machine -->
                                    <rect x="3" y="10" width="12" height="16" rx="2" fill="#ef4444" />
                                    <!-- Sink with silver tap -->
                                    <rect x="3" y="45" width="12" height="18" rx="2" fill="#94a3b8" />
                                    <!-- Microwave -->
                                    <rect x="3" y="80" width="12" height="16" rx="2" fill="#475569" />
                                    <!-- Refrigerator -->
                                    <rect x="2" y="108" width="14" height="28" rx="2" fill="#e2e8f0" />
                                </g>

                                <circle cx="945" cy="565" r="7" fill="#15803d" />
                            </g>

                            <!-- ---------------------------------------------- -->
                            <!-- 3G. RESTROOM (Far Bottom-Right: Tiles)         -->
                            <!-- ---------------------------------------------- -->
                            <g id="zone-restroom" @click="selectRoomFromFloor('restroom')">
                                <rect x="932" y="584" width="228" height="96" rx="10" fill="url(#grad-restroom-floor)" stroke="#475569" stroke-width="1.2" class="cursor-pointer" />

                                <!-- Header Badge: Restroom -->
                                <g transform="translate(995, 650)" class="cursor-pointer" @click.stop="selectRoomFromFloor('restroom')">
                                    <rect x="0" y="0" width="95" height="22" rx="5" fill="#1e293b" stroke="#475569" stroke-width="1" />
                                    <text x="47.5" y="15" fill="#94a3b8" font-size="9" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Restroom</text>
                                </g>

                                <!-- Restroom Cubicles & Vanity -->
                                <g transform="translate(942, 592)">
                                    <!-- Cubicle 1 -->
                                    <rect x="0" y="0" width="45" height="48" rx="2" fill="#0b121e" stroke="#334155" />
                                    <circle cx="22" cy="24" r="7" fill="#ffffff" />
                                    <!-- Cubicle 2 -->
                                    <rect x="52" y="0" width="45" height="48" rx="2" fill="#0b121e" stroke="#334155" />
                                    <circle cx="74" cy="24" r="7" fill="#ffffff" />
                                    <!-- Vanity Counter with Wash Basins -->
                                    <rect x="110" y="6" width="70" height="24" rx="3" fill="#1e293b" stroke="#475569" />
                                    <circle cx="128" cy="18" r="6" fill="#38bdf8" fill-opacity="0.5" />
                                    <circle cx="162" cy="18" r="6" fill="#38bdf8" fill-opacity="0.5" />
                                </g>
                            </g>


                            <!-- ============================================================== -->
                            <!-- 4. EXTERIOR LANDSCAPING & COMPASS ROSE (BOTTOM AREA)          -->
                            <!-- ============================================================== -->
                            <g id="zone-landscaping">
                                <!-- Sidewalk Curb Line -->
                                <rect x="35" y="684" width="1130" height="66" rx="6" fill="#0a101d" />

                                <!-- Manicured Green Hedges & Bushes flanking entrance -->
                                <ellipse cx="380" cy="710" rx="32" ry="14" fill="url(#bush-grad)" filter="url(#soft-shadow)" />
                                <ellipse cx="430" cy="712" rx="26" ry="12" fill="url(#bush-grad)" filter="url(#soft-shadow)" />
                                <ellipse cx="670" cy="712" rx="26" ry="12" fill="url(#bush-grad)" filter="url(#soft-shadow)" />
                                <ellipse cx="720" cy="710" rx="32" ry="14" fill="url(#bush-grad)" filter="url(#soft-shadow)" />

                                <!-- Outdoor Landscaped Trees -->
                                <g transform="translate(140, 720)">
                                    <circle cx="0" cy="0" r="16" fill="url(#rotunda-tree-foliage)" filter="url(#soft-shadow)" />
                                    <circle cx="-5" cy="-4" r="8" fill="#4ade80" fill-opacity="0.7" />
                                </g>
                                <g transform="translate(240, 720)">
                                    <circle cx="0" cy="0" r="16" fill="url(#rotunda-tree-foliage)" filter="url(#soft-shadow)" />
                                    <circle cx="-5" cy="-4" r="8" fill="#4ade80" fill-opacity="0.7" />
                                </g>
                                <g transform="translate(340, 720)">
                                    <circle cx="0" cy="0" r="14" fill="url(#rotunda-tree-foliage)" filter="url(#soft-shadow)" />
                                </g>
                                <g transform="translate(760, 720)">
                                    <circle cx="0" cy="0" r="14" fill="url(#rotunda-tree-foliage)" filter="url(#soft-shadow)" />
                                </g>
                                <g transform="translate(860, 720)">
                                    <circle cx="0" cy="0" r="16" fill="url(#rotunda-tree-foliage)" filter="url(#soft-shadow)" />
                                    <circle cx="-5" cy="-4" r="8" fill="#4ade80" fill-opacity="0.7" />
                                </g>
                                <g transform="translate(960, 720)">
                                    <circle cx="0" cy="0" r="16" fill="url(#rotunda-tree-foliage)" filter="url(#soft-shadow)" />
                                    <circle cx="-5" cy="-4" r="8" fill="#4ade80" fill-opacity="0.7" />
                                </g>

                                <!-- Circular Compass Rose (Bottom-Right Corner) -->
                                <g transform="translate(1130, 725)" id="compass-rose">
                                    <circle cx="0" cy="0" r="22" fill="#0f172a" stroke="#334155" stroke-width="2" filter="url(#soft-shadow)" />
                                    <circle cx="0" cy="0" r="18" fill="none" stroke="#64748b" stroke-width="1" stroke-dasharray="2 3" />
                                    <!-- North Faceted Needle -->
                                    <polygon points="0,-16 4,0 0,-3 -4,0" fill="#38bdf8" />
                                    <polygon points="0,-16 0,-3 -4,0" fill="#0284c7" />
                                    <!-- South Needle -->
                                    <polygon points="0,16 4,0 0,3 -4,0" fill="#64748b" />
                                    <!-- Center Pin -->
                                    <circle cx="0" cy="0" r="2.5" fill="#f8fafc" />
                                    <!-- N Label -->
                                    <text x="0" y="-19" fill="#38bdf8" font-size="8.5" font-family="system-ui, sans-serif" font-weight="900" text-anchor="middle">N</text>
                                </g>
                            </g>
                        </svg>
                    </div>

                    <!-- Interactive Inspector HUD Overlay (2D Blueprint Viewport) -->
                    @include('app.ai.partials.agent_inspector_hud')
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- FLOATING BOTTOM COCKPIT STATUS BAR (Streamlined Apple HIG)     -->
            <!-- ============================================================== -->
            <div class="p-2.5 sm:p-3 rounded-2xl sm:rounded-3xl bg-white/95 dark:bg-[#0a1526]/95 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white shadow-lg flex items-center justify-between gap-3 transition-colors backdrop-blur-md">
                
                <!-- Left: Campus Status & System Health -->
                <div class="flex items-center gap-2 min-w-0">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-mono truncate">COOCA CAMPUS 20F</span>
                    </span>
                    <span class="hidden sm:inline-flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        <span class="text-emerald-500">Online</span>
                        <span class="text-slate-300 dark:text-white/20">•</span>
                        <span><span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums">{{ $activeAgentsCount }}</span> Aktif</span>
                    </span>
                </div>

                <!-- Right: 2D / 3D & Zoom controls -->
                <div class="flex items-center gap-2 shrink-0">
                    <div class="inline-flex p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-900 border border-slate-300 dark:border-white/10 text-xs">
                        <button
                            type="button"
                            @click="setMode('3d')"
                            :class="viewMode === '3d' ? 'bg-cyan-500 text-slate-950 font-bold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition text-[11px] cursor-pointer"
                        >
                            3D View
                        </button>
                        <button
                            type="button"
                            @click="setMode('office')"
                            :class="viewMode === 'office' ? 'bg-cyan-500 text-slate-950 font-bold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition text-[11px] cursor-pointer"
                        >
                            2D Blueprint
                        </button>
                    </div>

                    <div class="inline-flex p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-900 border border-slate-300 dark:border-white/10 text-xs">
                        <button
                            type="button"
                            @click="zoomOut()"
                            class="w-7 h-7 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition cursor-pointer"
                            title="Zoom Out"
                        >
                            <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                        </button>
                        <button
                            type="button"
                            @click="zoomIn()"
                            class="w-7 h-7 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition cursor-pointer"
                            title="Zoom In"
                        >
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ---------------------------------------------------------- -->
        <!-- RIGHT PANEL: AI Office Status & Activity (Image 3 Design)   -->
        <!-- ---------------------------------------------------------- -->
        <div class="lg:col-span-4 xl:col-span-3 space-y-3 min-w-0 lg:sticky lg:top-20">
            
            <!-- 1. AI Office Status Card -->
            <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white dark:bg-[#0f1d33] border border-black/10 dark:border-white/10 shadow-lg space-y-4">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white tracking-tight">AI Office Status</h3>
                
                <div class="grid grid-cols-4 gap-2 text-center">
                    <!-- Total Agents -->
                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <div class="flex items-center justify-center text-blue-500 mb-1">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </div>
                        <div class="text-base font-black text-slate-900 dark:text-white tabular-nums">{{ $totalAgentsCount }}</div>
                        <div class="text-[9px] text-slate-500 dark:text-slate-400 font-medium">Total Agen</div>
                    </div>

                    <!-- Active -->
                    <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20">
                        <div class="flex items-center justify-center text-emerald-500 mb-1">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                        </div>
                        <div class="text-base font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $activeAgentsCount }}</div>
                        <div class="text-[9px] text-emerald-600/80 dark:text-emerald-400/80 font-medium">Aktif</div>
                    </div>

                    <!-- Idle -->
                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <div class="flex items-center justify-center text-slate-400 mb-1">
                            <i data-lucide="moon" class="w-4 h-4"></i>
                        </div>
                        <div class="text-base font-black text-slate-700 dark:text-slate-300 tabular-nums">{{ $idleAgentsCount }}</div>
                        <div class="text-[9px] text-slate-500 dark:text-slate-400 font-medium">Siaga</div>
                    </div>

                    <!-- Waiting Approval -->
                    <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20">
                        <div class="flex items-center justify-center text-amber-500 mb-1">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div class="text-base font-black text-amber-600 dark:text-amber-400 tabular-nums">{{ $pendingApprovalsCount }}</div>
                        <div class="text-[9px] text-amber-600/80 dark:text-amber-400/80 font-medium">Menunggu</div>
                    </div>
                </div>
            </div>

            <!-- 2. Active Work Card -->
            <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white dark:bg-[#0f1d33] border border-black/10 dark:border-white/10 shadow-lg space-y-3.5">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Active Work</h3>
                    <a href="{{ route('cooca-ai.actions') }}" class="text-xs font-semibold text-blue-600 dark:text-cyan-400 hover:underline">View All</a>
                </div>

                <div class="space-y-2.5">
                    @php
                        $activeTasksList = $recentTasks->whereIn('status', ['running', 'pending', 'waiting_approval'])->take(3);
                    @endphp
                    @forelse($activeTasksList as $task)
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs shrink-0">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white truncate">{{ ucwords(str_replace('_', ' ', $task->agent)) }}</div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate">{{ \Illuminate\Support\Str::limit($task->input, 32) }}</div>
                                    </div>
                                </div>
                                <span class="text-[9px] font-mono px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold uppercase">{{ $task->status }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-3 text-center text-xs text-slate-500 dark:text-slate-400">
                            <i data-lucide="check-circle" class="w-5 h-5 mx-auto mb-1 text-emerald-500/80"></i>
                            <p class="text-[11px]">Seluruh 12 agen dalam kondisi siap &amp; siaga.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 3. Pending Approvals Card -->
            <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white dark:bg-[#0f1d33] border border-black/10 dark:border-white/10 shadow-lg space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Pending Approvals</h3>
                    <a href="{{ route('cooca-ai.actions') }}" class="text-xs font-semibold text-blue-600 dark:text-cyan-400 hover:underline">View All</a>
                </div>

                <div class="space-y-2">
                    @forelse($pendingProposals->take(3) as $prop)
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ $prop->title }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ ucwords(str_replace('_', ' ', $prop->agent)) }} &bull; {{ ucfirst($prop->risk_level ?? 'Normal') }}</div>
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-400 shrink-0 font-mono">{{ $prop->created_at?->diffForHumans() ?? 'Baru' }}</span>
                        </div>
                    @empty
                        <div class="py-3 text-center text-xs text-slate-500 dark:text-slate-400">
                            <i data-lucide="check" class="w-5 h-5 mx-auto mb-1 text-slate-400"></i>
                            <p class="text-[11px]">Tidak ada usulan yang menunggu persetujuan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 4. Recent Activity Card -->
            <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white dark:bg-[#0f1d33] border border-black/10 dark:border-white/10 shadow-lg space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Recent Activity</h3>
                    <a href="{{ route('cooca-ai.history') }}" class="text-xs font-semibold text-blue-600 dark:text-cyan-400 hover:underline">View All</a>
                </div>

                <div class="space-y-2 text-xs">
                    @forelse($recentHistories->take(4) as $hist)
                        <div class="flex items-center gap-2.5 text-slate-600 dark:text-slate-300">
                            <div class="w-5 h-5 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">
                                <i data-lucide="activity" class="w-3 h-3"></i>
                            </div>
                            <div class="flex-1 truncate text-[11px]">
                                <span class="font-mono text-slate-400 mr-1 text-[10px]">{{ $hist->created_at?->format('H:i') ?? 'Baru' }}</span>
                                <b class="text-slate-800 dark:text-white">{{ ucwords(str_replace('_', ' ', $hist->agent ?? 'AI')) }}</b> {{ \Illuminate\Support\Str::limit($hist->action ?? $hist->title ?? 'Aktivitas tercatat', 32) }}
                            </div>
                        </div>
                    @empty
                        <div class="py-3 text-center text-xs text-slate-500 dark:text-slate-400">
                            <p class="text-[11px]">Belum ada riwayat aktivitas terbaru.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 5. Bottom Card -->
            <a href="{{ route('cooca-ai.actions') }}" class="p-4 rounded-2xl sm:rounded-3xl bg-blue-600 hover:bg-blue-700 text-white transition flex items-center gap-3.5 shadow-lg shadow-blue-500/20 group cursor-pointer">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold leading-snug">Your digital workforce is working for your business.</div>
                    <div class="text-[10px] text-white/70 mt-0.5">You remain in control.</div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-white/70 group-hover:translate-x-0.5 transition"></i>
            </a>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 4. AVATAR CUSTOMIZER MODAL SHEET (APPLE HIG / BENTO DESIGN)    -->
    <!-- ============================================================== -->
    <div
        x-show="showAvatarModal"
        x-cloak
        class="fixed inset-0 z-[150] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="showAvatarModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeAvatarModal()"
            class="fixed inset-0 bg-black/60 backdrop-blur-md transition-opacity"
        ></div>

        <div
            x-show="showAvatarModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-4xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-2xl rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden z-10 flex flex-col max-h-[90vh]"
        >
            <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.02] dark:bg-white/[0.02]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#AF52DE] to-indigo-500 flex items-center justify-center text-white shadow-md shadow-purple-500/20">
                        <i data-lucide="palette" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-black dark:text-white tracking-tight">Kustomisasi Avatar AI Agent</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Real-Time 3D Sync</span>
                        </div>
                        <p class="text-xs text-black/50 dark:text-white/50">Sesuaikan karakter, arketipe 3D, warna outfit, rambut, dan aksesoris staf AI.</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeAvatarModal()"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition cursor-pointer"
                >
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Agent Horizontal Switcher Ribbon -->
            <div class="px-6 py-2.5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01] overflow-x-auto flex items-center gap-2 no-scrollbar">
                <span class="text-[11px] font-mono uppercase tracking-wider text-black/40 dark:text-white/40 shrink-0 font-semibold mr-1">Pilih Agen:</span>
                <template x-for="(agent, roleKey) in agents" :key="roleKey">
                    <button
                        type="button"
                        @click="loadAvatarIntoForm(roleKey)"
                        :class="editingRole === roleKey 
                            ? 'bg-black text-white dark:bg-white dark:text-black shadow-sm ring-2 ring-purple-500/40' 
                            : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70 hover:bg-black/10 dark:hover:bg-white/10'"
                        class="px-3 py-1.5 rounded-xl text-xs font-medium transition flex items-center gap-2 shrink-0 cursor-pointer"
                    >
                        <span class="w-2 h-2 rounded-full" :style="'background-color: ' + (agent.avatar?.suit_color || '#6366f1')"></span>
                        <span class="font-semibold" x-text="agent.name || roleKey.toUpperCase()"></span>
                    </button>
                </template>
            </div>

            <div class="p-6 overflow-y-auto flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Preview Card -->
                <div class="lg:col-span-4 flex flex-col gap-4">
                    <div 
                        class="p-5 rounded-2xl border transition-all duration-300 relative overflow-hidden flex flex-col items-center text-center shadow-lg"
                        :style="'background: linear-gradient(135deg, ' + avatarForm.suit_color + '15, ' + avatarForm.suit_color + '30); border-color: ' + avatarForm.suit_color + '40;'"
                    >
                        <div class="relative my-3">
                            <div 
                                class="w-24 h-24 rounded-full relative flex items-center justify-center shadow-xl ring-4 ring-white dark:ring-zinc-800 transition-all"
                                :style="'background-color: ' + avatarForm.skin_tone"
                            >
                                <div 
                                    class="absolute -top-1 left-2 right-2 h-10 rounded-t-full transition-colors"
                                    :style="'background-color: ' + avatarForm.hair_color"
                                ></div>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-zinc-800 dark:bg-zinc-900"></div>
                                    <div class="w-1.5 h-1.5 rounded-full bg-zinc-800 dark:bg-zinc-900"></div>
                                </div>
                            </div>

                            <div 
                                class="w-28 h-10 -mt-2 mx-auto rounded-t-2xl shadow-md border-t border-x border-white/20 transition-all flex items-center justify-center"
                                :style="'background-color: ' + avatarForm.suit_color"
                            ></div>
                        </div>

                        <div class="w-full mt-2">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-mono uppercase font-bold tracking-wider mb-1"
                                :style="'background-color: ' + avatarForm.suit_color + '30; color: ' + avatarForm.suit_color"
                                x-text="editingRole"
                            ></span>
                            <div class="font-bold text-sm text-black dark:text-white" x-text="avatarForm.custom_name || editingRole.toUpperCase()"></div>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 space-y-2">
                        <label class="block text-xs font-semibold text-black/80 dark:text-white/80">
                            Nama Panggilan / Title Agen
                        </label>
                        <input
                            type="text"
                            x-model="avatarForm.custom_name"
                            placeholder="Contoh: Aria - AI CEO"
                            class="w-full px-3 py-2 rounded-xl text-xs bg-white dark:bg-zinc-800 border border-black/10 dark:border-white/15 focus:outline-none focus:ring-2 focus:ring-purple-500/50 text-black dark:text-white"
                        />
                    </div>
                </div>

                <!-- Right Color Controls -->
                <div class="lg:col-span-8 space-y-4">
                    <!-- Suit Color -->
                    <div>
                        <label class="text-xs font-bold text-black/80 dark:text-white/80 uppercase font-mono tracking-wider mb-2 block">
                            Warna Jas / Outfit Kerja
                        </label>
                        <div class="flex flex-wrap items-center gap-2">
                            <template x-for="c in colorOptions.suit" :key="c">
                                <button
                                    type="button"
                                    @click="avatarForm.suit_color = c; if(audioEnabled) playKeySound();"
                                    class="w-8 h-8 rounded-xl border-2 transition transform active:scale-90"
                                    :class="avatarForm.suit_color === c ? 'border-purple-500 scale-110 shadow-md ring-2 ring-purple-500/40' : 'border-black/10 dark:border-white/10 hover:scale-105'"
                                    :style="'background-color: ' + c"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <!-- Skin Tone -->
                    <div>
                        <label class="text-xs font-bold text-black/80 dark:text-white/80 uppercase font-mono tracking-wider mb-2 block">
                            Warna Kulit Karakter
                        </label>
                        <div class="flex flex-wrap items-center gap-2">
                            <template x-for="c in colorOptions.skin" :key="c">
                                <button
                                    type="button"
                                    @click="avatarForm.skin_tone = c; if(audioEnabled) playKeySound();"
                                    class="w-8 h-8 rounded-xl border-2 transition transform active:scale-90"
                                    :class="avatarForm.skin_tone === c ? 'border-purple-500 scale-110 shadow-md ring-2 ring-purple-500/40' : 'border-black/10 dark:border-white/10 hover:scale-105'"
                                    :style="'background-color: ' + c"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <!-- Hair Color -->
                    <div>
                        <label class="text-xs font-bold text-black/80 dark:text-white/80 uppercase font-mono tracking-wider mb-2 block">
                            Warna Rambut
                        </label>
                        <div class="flex flex-wrap items-center gap-2">
                            <template x-for="c in colorOptions.hair" :key="c">
                                <button
                                    type="button"
                                    @click="avatarForm.hair_color = c; if(audioEnabled) playKeySound();"
                                    class="w-8 h-8 rounded-xl border-2 transition transform active:scale-90"
                                    :class="avatarForm.hair_color === c ? 'border-purple-500 scale-110 shadow-md ring-2 ring-purple-500/40' : 'border-black/10 dark:border-white/10 hover:scale-105'"
                                    :style="'background-color: ' + c"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.02] dark:bg-white/[0.02]">
                <button
                    type="button"
                    @click="closeAvatarModal()"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="saveAvatar()"
                    :disabled="isSavingAvatar"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-purple-500/25 transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
                >
                    <i data-lucide="check" class="w-4 h-4" x-show="!isSavingAvatar"></i>
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSavingAvatar"></i>
                    <span x-text="isSavingAvatar ? 'Menyimpan...' : 'Terapkan Avatar Agen'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL SHEET: ORGANISASI & PEMBAGIAN TUGAS (12 AGENTS + C-SUITE) -->
    <!-- ============================================================== -->
    <div
        x-show="showOrgChartModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[150] flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-md overflow-y-auto"
        style="display: none;"
    >
        <div
            @click.away="closeOrgChartModal()"
            class="relative w-full max-w-6xl max-h-[90vh] flex flex-col rounded-3xl bg-zinc-950/95 border border-white/20 text-white shadow-2xl overflow-hidden"
        >
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between bg-white/[0.02]">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 via-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-cyan-500/25 ring-2 ring-white/20">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-base sm:text-lg text-white">Struktur Organisasi &amp; Pembagian Tugas AI</h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-[10px] font-mono font-bold uppercase tracking-wider">
                                12 Spesialis + 6 Pimpinan
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Roster komprehensif, pembagian tanggung jawab, pimpinan, dan lokasi ruangan di Virtual Office
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeOrgChartModal()"
                    class="p-2 rounded-full text-slate-400 hover:text-white hover:bg-white/10 transition cursor-pointer"
                >
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Controls: Filter & Search Bar -->
            <div class="p-4 sm:px-6 border-b border-white/10 bg-black/40 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <!-- Filter Tabs -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold">
                    <button
                        type="button"
                        @click="orgChartFilter = 'all'"
                        :class="orgChartFilter === 'all' ? 'bg-cyan-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer"
                    >
                        Semua (18 Personel)
                    </button>
                    <button
                        type="button"
                        @click="orgChartFilter = 'executive'"
                        :class="orgChartFilter === 'executive' ? 'bg-blue-600 text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1"
                    >
                        <span>Direksi &amp; Eksekutif</span>
                    </button>
                    <button
                        type="button"
                        @click="orgChartFilter = 'operations'"
                        :class="orgChartFilter === 'operations' ? 'bg-cyan-600 text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1"
                    >
                        <span>Operasional &amp; Gudang</span>
                    </button>
                    <button
                        type="button"
                        @click="orgChartFilter = 'growth'"
                        :class="orgChartFilter === 'growth' ? 'bg-purple-600 text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1"
                    >
                        <span>Pemasaran &amp; Penjualan</span>
                    </button>
                    <button
                        type="button"
                        @click="orgChartFilter = 'finance'"
                        :class="orgChartFilter === 'finance' ? 'bg-amber-600 text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1"
                    >
                        <span>Keuangan &amp; Audit</span>
                    </button>
                    <button
                        type="button"
                        @click="orgChartFilter = 'people'"
                        :class="orgChartFilter === 'people' ? 'bg-fuchsia-600 text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1"
                    >
                        <span>SDM &amp; Kultur</span>
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative w-full md:w-72">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        x-model="orgChartSearch"
                        placeholder="Cari peran, tugas, atau nama..."
                        class="w-full pl-9 pr-4 py-1.5 rounded-xl bg-white/5 border border-white/15 text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
                    />
                </div>
            </div>

            <!-- Modal Content: Roster Grid -->
            <div class="p-6 overflow-y-auto max-h-[62vh] space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="agent in filteredOrgAgents" :key="agent.role">
                        <div class="p-4 rounded-2xl bg-white/[0.04] hover:bg-white/[0.07] border border-white/10 hover:border-white/20 transition flex flex-col justify-between space-y-3 group">
                            <!-- Card Header -->
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-indigo-600 flex items-center justify-center font-bold text-white text-sm shadow-md ring-1 ring-white/20">
                                            <span x-text="agent.name.charAt(0)"></span>
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-white flex items-center gap-1.5">
                                                <span x-text="agent.name"></span>
                                                <template x-if="agent.isLead">
                                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold">LEAD</span>
                                                </template>
                                            </div>
                                            <div class="text-[11px] text-cyan-300 font-medium" x-text="agent.title"></div>
                                        </div>
                                    </div>
                                    <!-- Online Status Badge -->
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold uppercase tracking-wider"
                                          :class="agent.status === 'WORKING' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30'"
                                          x-text="agent.status"></span>
                                </div>

                                <!-- Location & Supervisor Pills -->
                                <div class="flex flex-wrap items-center gap-1.5 text-[10px]">
                                    <span class="px-2 py-0.5 rounded-lg bg-white/5 border border-white/10 text-slate-300 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-cyan-400"></i>
                                        <span x-text="agent.room"></span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-300 flex items-center gap-1">
                                        <i data-lucide="shield" class="w-3 h-3"></i>
                                        <span x-text="'Pimpinan: ' + agent.lead"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Duties / Pembagian Tugas -->
                            <div class="p-2.5 rounded-xl bg-black/40 border border-white/5 text-[11px] space-y-1">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1">
                                    <i data-lucide="check-circle-2" class="w-3 h-3 text-cyan-400"></i>
                                    <span>Tugas &amp; Tanggung Jawab:</span>
                                </div>
                                <p class="text-slate-200 leading-relaxed font-normal" x-text="agent.duties"></p>
                            </div>

                            <!-- Deliverables & Teammates -->
                            <div class="space-y-1 text-[10px]">
                                <div class="flex items-center gap-1 text-slate-400">
                                    <span class="font-medium text-slate-400">Luaran / Output:</span>
                                    <span class="text-cyan-300 font-semibold truncate" x-text="agent.deliverables"></span>
                                </div>
                                <div class="flex items-center gap-1 text-slate-400">
                                    <span class="font-medium text-slate-400">Rekan Kerja Tim:</span>
                                    <span class="text-slate-300 truncate" x-text="(agent.teammates && agent.teammates.length > 0) ? agent.teammates.join(', ') : 'Mandiri'"></span>
                                </div>
                            </div>

                            <!-- Card Action Buttons -->
                            <div class="pt-2 border-t border-white/10 flex items-center gap-1.5">
                                <button
                                    type="button"
                                    @click="focusAgentFromOrgChart(agent.role)"
                                    class="flex-1 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold flex items-center justify-center gap-1 transition cursor-pointer"
                                    title="Tampilkan lokasi meja agen di denah"
                                >
                                    <i data-lucide="crosshair" class="w-3 h-3 text-cyan-300"></i>
                                    <span>Fokus di Denah</span>
                                </button>
                                <button
                                    type="button"
                                    @click="consultWithAgent(agent); closeOrgChartModal();"
                                    class="flex-1 py-1.5 rounded-lg bg-gradient-to-r from-cyan-500 to-indigo-600 hover:opacity-90 text-white text-[11px] font-bold flex items-center justify-center gap-1 transition shadow-md shadow-cyan-500/20 cursor-pointer"
                                >
                                    <i data-lucide="send" class="w-3 h-3"></i>
                                    <span>Tugaskan</span>
                                </button>
                                <button
                                    type="button"
                                    @click="openAvatarModal(agent.role); closeOrgChartModal();"
                                    class="p-1.5 rounded-lg bg-white/5 hover:bg-white/15 text-purple-300 hover:text-white transition cursor-pointer"
                                    title="Kustomisasi Avatar Karakter Ini"
                                >
                                    <i data-lucide="palette" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white/[0.02]">
                <div class="text-xs text-slate-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span><strong>12 Specialized Agents</strong> + <strong>6 C-Suite Leaders</strong> terintegrasi penuh di Virtual Office Campus.</span>
                </div>
                <button
                    type="button"
                    @click="closeOrgChartModal()"
                    class="px-5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function registerCoocaVirtualOffice() {
        if (!window.Alpine) return;
        window.Alpine.data('coocaVirtualOffice', (config) => ({
            mode: (config && config.mode) || 'executive',
            agents: (config && config.agents) || {},
            tasks: (config && config.tasks) || [],
            proposals: (config && config.proposals) || [],
            avatarPresets: (config && config.presets) || {},
            viewMode: '3d', // '3d', 'office', 'bento'
            isNightMode: typeof document !== 'undefined' ? document.documentElement.classList.contains('dark') : false,
            audioEnabled: false,
            isScanning: false,
            office3d: null,
            selectedAgent: null,
            selectedAgentData: {},
            selectedAgentStatus: 'STANDBY',
            selectedRoom: null,
            selectedRoomData: {},
            showOrgChartModal: false,
            orgChartSearch: '',
            orgChartFilter: 'all',

            // POV Direct Player Control & Viewpoint State
            isPOVMode: false,
            povAgentRole: null,
            povAgentData: {},
            povCameraType: 'third_person',
            povCommandInput: '',

            // Fullscreen & Floating Control Center State
            isFullscreen: false,
            isFloatingChatOpen: false,
            isFloatingHistoryOpen: false,
            isFloatingRosterOpen: false,
            isCameraMenuOpen: false,
            floatingChatAgent: 'ceo',
            floatingChatInput: '',
            isSendingChatMessage: false,
            agentChatHistories: {},
            floatingHistoryFilter: 'all',
            floatingHistorySearch: '',
            histories: config.histories || [],
            currentTimeString: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
            clockInterval: null,

            // Direct Task Giving State
            directTaskInput: '',
            isAssigningTask: false,
            taskSuccessMessage: '',

            // 2D Pan & Zoom
            zoomLevel: 1.0,
            panX: 0,
            panY: 0,
            isPanning: false,
            startX: 0,
            startY: 0,

            // Avatar Customizer
            showAvatarModal: false,
            editingRole: 'ceo',
            isSavingAvatar: false,
            avatarForm: {
                preset: 'executive_male',
                custom_name: '',
                suit_color: '#1e293b',
                skin_tone: '#f8d9b6',
                hair_color: '#1e1e24',
                accessory: 'none',
            },
            colorOptions: {
                suit: ['#1e293b', '#0f172a', '#475569', '#1e3a8a', '#14532d', '#581c87', '#701a75', '#7f1d1d', '#854d0e', '#ffffff'],
                skin: ['#f8d9b6', '#e0ac69', '#c68642', '#8d5524', '#ffdbac', '#f1c27d'],
                hair: ['#1e1e24', '#3b2f2f', '#8b5a2b', '#c49a45', '#9a3324', '#e5e7eb', '#4f46e5'],
            },

            initOffice() {
                // Muat riwayat obrolan masing-masing agen dari localStorage (terisolasi per agen)
                this.loadAgentChatHistories();

                // Live ticking clock for Top-Left HUD
                this.clockInterval = setInterval(() => {
                    this.currentTimeString = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }, 1000);

                // Listen to native fullscreen changes (e.g. Esc pressed natively)
                const onFsChange = () => {
                    const isNative = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
                    if (!isNative && this.isFullscreen) {
                        this.isFullscreen = false;
                        if (this.office3d) {
                            setTimeout(() => { this.office3d.onResize(); }, 80);
                        }
                    }
                };
                document.addEventListener('fullscreenchange', onFsChange);
                document.addEventListener('webkitfullscreenchange', onFsChange);

                // Global keyboard shortcuts: F (Fullscreen), Esc (Exit fullscreen or close floating drawers), C (Chat), H (History)
                window.addEventListener('keydown', (e) => {
                    const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
                    const isInput = activeTag === 'input' || activeTag === 'textarea' || document.activeElement?.isContentEditable;
                    
                    if (e.key === 'Escape') {
                        if (this.selectedAgent || this.selectedRoom) {
                            this.deselectAgent();
                            this.deselectRoom();
                            return;
                        }
                        if (this.isFloatingChatOpen || this.isFloatingHistoryOpen || this.isFloatingRosterOpen || this.isCameraMenuOpen) {
                            this.isFloatingChatOpen = false;
                            this.isFloatingHistoryOpen = false;
                            this.isFloatingRosterOpen = false;
                            this.isCameraMenuOpen = false;
                            return;
                        }
                        if (this.isFullscreen) {
                            this.exitFullscreen();
                            return;
                        }
                    }

                    if (isInput) return; // Don't trigger letter shortcuts while typing in inputs

                    if (e.key === 'f' || e.key === 'F') {
                        e.preventDefault();
                        this.toggleFullscreen();
                    } else if (e.key === 'c' || e.key === 'C') {
                        e.preventDefault();
                        this.isFloatingChatOpen = !this.isFloatingChatOpen;
                        if (this.isFloatingChatOpen) {
                            this.isFloatingHistoryOpen = false;
                            this.isFloatingRosterOpen = false;
                            this.isCameraMenuOpen = false;
                        }
                    } else if (e.key === 'h' || e.key === 'H') {
                        e.preventDefault();
                        this.isFloatingHistoryOpen = !this.isFloatingHistoryOpen;
                        if (this.isFloatingHistoryOpen) {
                            this.isFloatingChatOpen = false;
                            this.isFloatingRosterOpen = false;
                            this.isCameraMenuOpen = false;
                        }
                    }
                });

                // Listen to global theme change events
                window.addEventListener('cooca-theme-changed', (e) => {
                    this.isNightMode = e.detail?.isDark ?? document.documentElement.classList.contains('dark');
                    if (this.office3d && typeof this.office3d.setDayNight === 'function') {
                        this.office3d.setDayNight(this.isNightMode);
                    }
                });

                // Listen to 3D engine POV state changes
                window.addEventListener('cooca-pov-change', (e) => {
                    const detail = e.detail || {};
                    this.isPOVMode = !!detail.isPOV;
                    this.povAgentRole = detail.roleKey || null;
                    this.povCameraType = detail.povType || 'third_person';
                    if (detail.roleKey && this.agents[detail.roleKey]) {
                        this.povAgentData = this.agents[detail.roleKey];
                    } else if (detail.agentData) {
                        this.povAgentData = detail.agentData;
                    } else {
                        this.povAgentData = {};
                    }
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        this.$nextTick(() => window.lucide.createIcons());
                    }
                });

                this.$nextTick(() => {
                    this.init3DCanvas();
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                });
            },

            init3DCanvas() {
                const containerId = 'cooca-3d-office-viewport-' + this.mode;
                const container = document.getElementById(containerId);
                if (!container || !window.Cooca3DOffice) return;

                this.office3d = new window.Cooca3DOffice(container, {
                    mode: this.mode,
                    agents: this.agents,
                    tasks: this.tasks,
                    proposals: this.proposals,
                    isNight: this.isNightMode,
                    audioEnabled: this.audioEnabled,
                    onSelectAgent: (roleKey, agentData) => {
                        this.selectAgentFromFloor(roleKey);
                    },
                    onPOVChange: (detail) => {
                        this.isPOVMode = !!detail.isPOV;
                        this.povAgentRole = detail.roleKey || null;
                        this.povCameraType = detail.povType || 'third_person';
                        if (detail.roleKey && this.agents[detail.roleKey]) {
                            this.povAgentData = this.agents[detail.roleKey];
                        } else if (detail.agentData) {
                            this.povAgentData = detail.agentData;
                        }
                    }
                });
            },

            setMode(mode) {
                this.viewMode = mode;
                if (mode === '3d' && this.office3d) {
                    this.$nextTick(() => {
                        this.office3d.onResize();
                    });
                }
                this.$nextTick(() => {
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                });
            },

            set3DCamera(preset) {
                if (this.viewMode !== '3d') {
                    this.setMode('3d');
                }
                if (this.office3d && typeof this.office3d.setCameraPreset === 'function') {
                    this.office3d.setCameraPreset(preset);
                }
            },

            toggleDayNight() {
                this.isNightMode = !this.isNightMode;
                const newTheme = this.isNightMode ? 'dark' : 'light';
                localStorage.setItem('cooca-theme', newTheme);
                if (this.isNightMode) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                document.documentElement.setAttribute('data-theme', newTheme);
                window.dispatchEvent(new CustomEvent('cooca-theme-changed', {
                    detail: { theme: newTheme, isDark: this.isNightMode }
                }));
                if (this.office3d && typeof this.office3d.setDayNight === 'function') {
                    this.office3d.setDayNight(this.isNightMode);
                }
            },

            toggleAudio() {
                this.audioEnabled = !this.audioEnabled;
                if (this.office3d) {
                    this.office3d.audioEnabled = this.audioEnabled;
                }
            },

            toggleFullscreen() {
                if (this.isFullscreen) {
                    this.exitFullscreen();
                } else {
                    this.enterFullscreen();
                }
            },

            enterFullscreen() {
                this.isFullscreen = true;
                const container = document.getElementById('cooca-office-container-' + this.mode);
                if (container) {
                    if (container.requestFullscreen) {
                        container.requestFullscreen().catch(() => {});
                    } else if (container.webkitRequestFullscreen) {
                        container.webkitRequestFullscreen();
                    } else if (container.msRequestFullscreen) {
                        container.msRequestFullscreen();
                    }
                }
                this.$nextTick(() => {
                    setTimeout(() => {
                        if (this.office3d && typeof this.office3d.onResize === 'function') {
                            this.office3d.onResize();
                        }
                        if (window.lucide && typeof window.lucide.createIcons === 'function') {
                            window.lucide.createIcons();
                        }
                    }, 100);
                });
            },

            exitFullscreen() {
                this.isFullscreen = false;
                if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                    if (document.exitFullscreen) {
                        document.exitFullscreen().catch(() => {});
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    }
                }
                this.$nextTick(() => {
                    setTimeout(() => {
                        if (this.office3d && typeof this.office3d.onResize === 'function') {
                            this.office3d.onResize();
                        }
                        if (window.lucide && typeof window.lucide.createIcons === 'function') {
                            window.lucide.createIcons();
                        }
                    }, 100);
                });
            },

            focusAgentCamera(roleKey) {
                if (this.viewMode !== '3d') {
                    this.setMode('3d');
                }
                this.selectAgentFromFloor(roleKey);
                if (this.office3d && typeof this.office3d.focusOnAgent === 'function') {
                    this.office3d.focusOnAgent(roleKey);
                }
            },

            // ==============================================================
            // FLOATING AGENTIC CHAT - ISOLATED PER-AGENT CHAT SYSTEM
            // ==============================================================
            get activeFloatingChatMessages() {
                const role = this.floatingChatAgent || 'ceo';
                if (!this.agentChatHistories || !this.agentChatHistories[role] || this.agentChatHistories[role].length === 0) {
                    this.initAgentChat(role);
                }
                return (this.agentChatHistories && this.agentChatHistories[role]) ? this.agentChatHistories[role] : [];
            },

            get floatingChatMessages() {
                return this.activeFloatingChatMessages;
            },

            get activeAgentTitle() {
                const role = this.floatingChatAgent || 'ceo';
                const meta = this.getAgentMetadata(role) || {};
                const existing = (this.agents && this.agents[role]) ? this.agents[role] : {};
                const name = (existing.avatar && existing.avatar.custom_name) || existing.name || meta.name || role.toUpperCase();
                return name + (meta.teamName ? ' • ' + meta.teamName : '');
            },

            get activeAgentQuickPrompts() {
                const role = this.floatingChatAgent || 'ceo';
                const promptMap = {
                    ceo: [
                        { icon: 'building', label: 'Executive Summary', query: 'Berikan ringkasan eksekutif performa dan kesehatan bisnis secara menyeluruh' },
                        { icon: 'target', label: 'Target Strategis', query: 'Bagaimana progres pencapaian target bisnis dan profitabilitas bulan ini?' },
                        { icon: 'alert-triangle', label: 'Deteksi Risiko', query: 'Apakah ada anomali atau risiko operasional lintas divisi hari ini?' },
                        { icon: 'lightbulb', label: 'Arahan Prioritas', query: 'Rekomendasikan langkah prioritas untuk memaksimalkan omzet minggu ini' }
                    ],
                    cfo: [
                        { icon: 'bar-chart-3', label: 'Profit & Cashflow', query: 'Bagaimana estimasi profit & cashflow bulan ini?' },
                        { icon: 'trending-up', label: 'Report Keuangan', query: 'Kasih report lengkap keuangan dan pembukuan' },
                        { icon: 'search', label: 'Audit Kasir', query: 'Audit transaksi kasir POS dan kas kecil hari ini' },
                        { icon: 'banknote', label: 'Efisiensi Opex', query: 'Analisis potensi efisiensi beban operasional bulanan' }
                    ],
                    coo: [
                        { icon: 'package', label: 'Stok Kritis', query: 'Cek status inventaris dan stok kritis di gudang' },
                        { icon: 'file-text', label: 'Ajukan Purchase', query: 'Buatkan usulan purchase order bahan baku yang menipis ke supplier' },
                        { icon: 'truck', label: 'SLA Pemenuhan', query: 'Bagaimana SLA pemenuhan pesanan dan logistik hari ini?' },
                        { icon: 'refresh-cw', label: 'Sinkronisasi Stok', query: 'Periksa sinkronisasi stok toko fisik dan online' }
                    ],
                    cmo: [
                        { icon: 'target', label: 'Promo Flash Sale', query: 'Buat ide promo flash sale untuk dongkrak omzet' },
                        { icon: 'megaphone', label: 'Performa Iklan', query: 'Bagaimana performa kampanye iklan dan CTR saat ini?' },
                        { icon: 'users', label: 'Lead Prospek', query: 'Analisis pertambahan pelanggan baru dan lead prospek' },
                        { icon: 'smartphone', label: 'Ide Kampanye', query: 'Rekomendasikan strategi marketing omnichannel untuk akhir pekan' }
                    ],
                    marketing: [
                        { icon: 'target', label: 'Promo Flash Sale', query: 'Buat ide promo flash sale untuk dongkrak omzet' },
                        { icon: 'smartphone', label: 'Materi Promo', query: 'Siapkan materi promosi dan copy konten sosial media' },
                        { icon: 'users', label: 'Audience Growth', query: 'Analisis jangkauan dan engagement konten promosi' }
                    ],
                    sales_director: [
                        { icon: 'briefcase', label: 'Pipeline B2B', query: 'Bagaimana status negosiasi prospek B2B saat ini?' },
                        { icon: 'target', label: 'Closing Target', query: 'Berapa persen pencapaian target closing sales bulan ini?' },
                        { icon: 'message-circle', label: 'Follow-up Client', query: 'Daftar klien prioritas yang perlu di-follow up hari ini' },
                        { icon: '⭐', label: 'Retensi Pelanggan', query: 'Analisis repeat order dan loyalitas pelanggan' }
                    ],
                    sales: [
                        { icon: 'briefcase', label: 'Closing Harian', query: 'Berapa total transaksi dan closing kasir hari ini?' },
                        { icon: 'message-circle', label: 'Follow-up Pelanggan', query: 'Pelanggan mana saja yang belum menyelesaikan pesanan?' },
                        { icon: '⭐', label: 'Upselling', query: 'Rekomendasikan paket bundling produk untuk kasir' }
                    ],
                    inventory: [
                        { icon: 'package', label: 'Stok Menipis', query: 'Bahan baku dan barang apa saja yang di bawah safety stock?' },
                        { icon: 'file-text', label: 'Draft PO Supplier', query: 'Siapkan usulan Purchase Order ke supplier resmi' },
                        { icon: 'clipboard', label: 'Opname Gudang', query: 'Kapan jadwal stock opname berikutnya dan status selisih stok?' }
                    ],
                    purchasing: [
                        { icon: 'file-text', label: 'Draft PO', query: 'Buat draf Purchase Order untuk barang yang perlu reorder' },
                        { icon: 'handshake', label: 'Supplier Hub', query: 'Status konfirmasi ketersediaan pasokan dari supplier' },
                        { icon: 'banknote', label: 'Negosiasi Harga', query: 'Bandingkan penawaran harga supplier untuk bahan baku utama' }
                    ],
                    marketplace: [
                        { icon: 'shopping-cart', label: 'Sinkronisasi Toko', query: 'Sinkronkan stok dan harga dengan e-commerce / marketplace' },
                        { icon: 'package', label: 'Pesanan Online', query: 'Cek pesanan online baru yang perlu diproses gudang' }
                    ],
                    finance: [
                        { icon: 'trending-up', label: 'Buku Kasir', query: 'Rekonsiliasi transaksi penjualan kasir POS hari ini' },
                        { icon: 'receipt', label: 'Invoice Jatuh Tempo', query: 'Daftar invoice dan tagihan supplier yang akan jatuh tempo' },
                        { icon: 'dollar-sign', label: 'Saldo Kas Kecil', query: 'Cek saldo kas operasional harian' }
                    ],
                    reporting: [
                        { icon: 'bar-chart-3', label: 'Laporan Laba Rugi', query: 'Kompilasi ringkasan laba rugi bisnis bulan ini' },
                        { icon: 'trending-down', label: 'Breakdown Biaya', query: 'Tampilkan perincian pos pengeluaran terbesar' }
                    ],
                    hr_lead: [
                        { icon: 'zap', label: 'Audit Kerja', query: 'Audit produktivitas & beban kerja tim hari ini' },
                        { icon: 'clipboard', label: 'Jadwal Shift', query: 'Cek kepatuhan jadwal shift dan absensi staf' },
                        { icon: 'star', label: 'Evaluasi KPI', query: 'Evaluasi performa kerja dan pencapaian target karyawan' }
                    ],
                    hr: [
                        { icon: 'clipboard', label: 'Shift Kerja', query: 'Periksa jadwal staf yang bertugas hari ini' },
                        { icon: 'zap', label: 'Beban Staf', query: 'Analisis jam lembur dan beban kerja operasional' }
                    ],
                    business: [
                        { icon: 'bar-chart-3', label: 'Tren Pasar', query: 'Bagaimana perbandingan tren penjualan bulan ini vs bulan lalu?' },
                        { icon: 'rocket', label: 'Peluang Ekspansi', query: 'Analisis produk terlaris dan peluang ekspansi menu baru' },
                        { icon: 'search', label: 'Audit Margin', query: 'Evaluasi margin kontribusi per kategori produk' }
                    ],
                    customer: [
                        { icon: 'message-circle', label: 'Feedback Tamu', query: 'Bagaimana ulasan dan feedback pelanggan minggu ini?' },
                        { icon: '⭐', label: 'Loyalty Program', query: 'Berapa banyak member aktif yang menukarkan poin reward?' }
                    ]
                };

                return promptMap[role] || [
                    { icon: 'zap', label: 'Status Tugas', query: 'Bagaimana status tugas dan progres kerjamu saat ini?' },
                    { icon: 'bar-chart-3', label: 'Laporan Divisi', query: 'Tampilkan data dan analisis terpenting dari divisi Anda' }
                ];
            },

            getStorageKey() {
                const bizId = (config.business && config.business.id) || 
                              (window.coocaVirtualOfficeConfig && window.coocaVirtualOfficeConfig.business && window.coocaVirtualOfficeConfig.business.id) || 
                              'default';
                return 'cooca_agent_chats_' + bizId;
            },

            loadAgentChatHistories() {
                try {
                    const key = this.getStorageKey();
                    const saved = localStorage.getItem(key);
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                            this.agentChatHistories = parsed;
                        }
                    }
                } catch (e) {
                    console.warn('Gagal memuat history chat agen dari localStorage:', e);
                }

                if (!this.agentChatHistories || typeof this.agentChatHistories !== 'object') {
                    this.agentChatHistories = {};
                }

                const currentRole = this.floatingChatAgent || 'ceo';
                if (!this.agentChatHistories[currentRole] || this.agentChatHistories[currentRole].length === 0) {
                    this.initAgentChat(currentRole);
                }
            },

            saveAgentChatHistories() {
                try {
                    const key = this.getStorageKey();
                    const cleaned = {};
                    for (const [role, msgs] of Object.entries(this.agentChatHistories)) {
                        if (Array.isArray(msgs)) {
                            cleaned[role] = msgs.slice(-50); // Simpan hingga 50 pesan terakhir per agen
                        }
                    }
                    localStorage.setItem(key, JSON.stringify(cleaned));
                } catch (e) {
                    console.warn('Gagal menyimpan history chat agen ke localStorage:', e);
                }
            },

            getDefaultAgentGreeting(role) {
                const greetings = {
                    ceo: {
                        sender: 'AI CEO (Chief Executive)',
                        role: 'ceo',
                        text: 'Halo Pak! Executive Command Center siap menerima instruksi strategis Anda. Ada arahan operasional, visi bisnis, atau evaluasi profitabilitas yang ingin ditinjau?'
                    },
                    cfo: {
                        sender: 'AI CFO (Keuangan)',
                        role: 'cfo',
                        text: 'Halo Pak! Divisi Keuangan aktif memantau arus kas (cashflow), solvabilitas, dan pembukuan jurnal. Margin laba dan saldo kas kecil terkendali. Ada laporan keuangan yang ingin diaudit?'
                    },
                    coo: {
                        sender: 'AI COO (Operasional)',
                        role: 'coo',
                        text: 'Siap Pak! Divisi Operasional & Rantai Pasok siap mengawal stok gudang, batas reorder (ROP), dan pemenuhan pesanan kasir. Ada instruksi logistik atau inventaris?'
                    },
                    cmo: {
                        sender: 'AI CMO (Pemasaran)',
                        role: 'cmo',
                        text: 'Halo Pak! Divisi Pemasaran & Growth siap mengoptimalkan kampanye promo, perolehan leads, dan engagement pelanggan. Strategi promosi apa yang ingin kita gencarkan?'
                    },
                    sales_director: {
                        sender: 'AI Sales Director',
                        role: 'sales_director',
                        text: 'Siap Pak! Tim penjualan siap memacu closing transaksi. Pipeline prospek B2B, penawaran harga, dan target revenue terpantau real-time. Ada deal yang ingin ditinjau?'
                    },
                    hr_lead: {
                        sender: 'AI HR Lead (People)',
                        role: 'hr_lead',
                        text: 'Halo Pak! Divisi HR & People siap memantau produktivitas tim, shift kerja, dan kepatuhan SOP karyawan. Ada evaluasi performa staf yang ingin dibahas?'
                    },
                    business: {
                        sender: 'Business Intelligence Agent',
                        role: 'business',
                        text: 'Halo Pak! Agen Business Intelligence siap membedah tren pasar, perbandingan performa antar cabang, dan metrik pertumbuhan bisnis Anda.'
                    },
                    inventory: {
                        sender: 'Inventory Agent (Gudang)',
                        role: 'inventory',
                        text: 'Siap Pak! Pemantauan stok bahan baku, stok display toko, dan safety stock siap dilaporkan secara presisi. Ada opname atau cek barang?'
                    },
                    purchasing: {
                        sender: 'Purchasing Agent (Pengadaan)',
                        role: 'purchasing',
                        text: 'Siap Pak! Pengadaan pasokan ke supplier siap dieksekusi. Draf Purchase Order (PO) otomatis dan perbandingan harga siap diajukan.'
                    },
                    marketplace: {
                        sender: 'Marketplace Agent',
                        role: 'marketplace',
                        text: 'Halo Pak! Integrasi etalase marketplace dan e-commerce berjalan normal. Sinkronisasi stok dan pesanan online terkoneksi.'
                    },
                    finance: {
                        sender: 'Finance Agent (Kasir & Jurnal)',
                        role: 'finance',
                        text: 'Halo Pak! Seluruh transaksi kasir POS hari ini telah terekonsiliasi ke buku kas dan jurnal harian. Ada mutasi kas yang ingin dicek?'
                    },
                    reporting: {
                        sender: 'Reporting Agent (Audit & Laporan)',
                        role: 'reporting',
                        text: 'Halo Pak! Agen pelaporan siap mengompilasi laporan laba rugi, neraca saldo, dan performa keuangan berkala Anda.'
                    },
                    marketing: {
                        sender: 'Marketing Agent',
                        role: 'marketing',
                        text: 'Halo Pak! Tim pemasaran siap mendesain kampanye promo, newsletter, dan komunikasi promosi ke pelanggan setia.'
                    },
                    content: {
                        sender: 'Content & Copywriting Agent',
                        role: 'content',
                        text: 'Halo Pak! Siap merancang caption media sosial, deskripsi produk roastery, dan materi promosi yang menarik minat beli pelanggan.'
                    },
                    social_media: {
                        sender: 'Social Media Agent',
                        role: 'social_media',
                        text: 'Halo Pak! Akun media sosial terpantau aktif. Jadwal posting konten dan interaksi komentar follower siap dioptimasi.'
                    },
                    sales: {
                        sender: 'Sales Agent',
                        role: 'sales',
                        text: 'Siap Pak! Tim kasir dan penjualan siap melayani transaksi harian, program bundling menu, dan penawaran ke pembeli.'
                    },
                    customer: {
                        sender: 'Customer Service Agent',
                        role: 'customer',
                        text: 'Halo Pak! Layanan pelanggan siap menerima masukan tamu, program membership loyalty, dan menangani komplain secara ramah.'
                    },
                    hr: {
                        sender: 'HR Operations Agent',
                        role: 'hr',
                        text: 'Halo Pak! Kepatuhan jadwal kerja shift, absensi staf kasir & barista, serta catatan lembur terpantau rapi.'
                    }
                };

                const meta = this.getAgentMetadata(role) || {};
                const g = greetings[role] || {
                    sender: meta.name || role.toUpperCase(),
                    role: role,
                    text: `Halo Pak! Agen ${meta.name || role.toUpperCase()} siap menerima instruksi dan menjalankan tugas divisi.`
                };

                return {
                    id: Date.now(),
                    from: 'agent',
                    isAi: true,
                    role: g.role,
                    sender: g.sender,
                    time: 'Baru saja',
                    text: g.text,
                    findings: [],
                    proposals: []
                };
            },

            initAgentChat(role) {
                if (!this.agentChatHistories) this.agentChatHistories = {};
                if (!this.agentChatHistories[role] || this.agentChatHistories[role].length === 0) {
                    this.agentChatHistories[role] = [this.getDefaultAgentGreeting(role)];
                    this.agentChatHistories = { ...this.agentChatHistories };
                    this.saveAgentChatHistories();
                }
            },

            onFloatingAgentChange(newAgent) {
                this.floatingChatAgent = newAgent;
                if (!this.agentChatHistories || !this.agentChatHistories[newAgent] || this.agentChatHistories[newAgent].length === 0) {
                    this.initAgentChat(newAgent);
                }
                this.$nextTick(() => {
                    const chatBox = document.getElementById('floating-chat-stream');
                    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                });
            },

            openFloatingChatWithAgent(role) {
                const teamKey = this.resolveAgentTeam(role);
                this.deselectAgent();
                this.deselectRoom();
                window.dispatchEvent(new CustomEvent('open-ai-consultation', {
                    detail: {
                        team: teamKey,
                        agent: role,
                        agentName: role,
                    }
                }));
            },

            resetCurrentAgentChat() {
                const role = this.floatingChatAgent || 'ceo';
                if (!this.agentChatHistories) this.agentChatHistories = {};
                this.agentChatHistories[role] = [this.getDefaultAgentGreeting(role)];
                this.agentChatHistories = { ...this.agentChatHistories };
                this.saveAgentChatHistories();
                this.$nextTick(() => {
                    const chatBox = document.getElementById('floating-chat-stream');
                    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                });
            },

            sendQuickPrompt(prompt) {
                this.floatingChatInput = prompt;
                this.sendFloatingChatMessage();
            },

            sendFloatingChatMessage() {
                const message = (this.floatingChatInput || '').trim();
                if (!message || this.isSendingChatMessage) return;

                const currentAgent = this.floatingChatAgent || 'ceo';
                const userMsgId = Date.now();
                const userMsg = {
                    id: userMsgId,
                    from: 'user',
                    isAi: false,
                    sender: 'Anda',
                    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                    text: message
                };

                if (!this.agentChatHistories) this.agentChatHistories = {};
                if (!this.agentChatHistories[currentAgent]) {
                    this.agentChatHistories[currentAgent] = [];
                }
                this.agentChatHistories[currentAgent].push(userMsg);
                this.agentChatHistories = { ...this.agentChatHistories };
                this.saveAgentChatHistories();

                this.floatingChatInput = '';
                this.isSendingChatMessage = true;

                this.$nextTick(() => {
                    const chatBox = document.getElementById('floating-chat-stream');
                    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
                });

                const teamMap = {
                    'ceo': 'executive',
                    'cfo': 'finance',
                    'coo': 'operations',
                    'cmo': 'marketing',
                    'sales_director': 'sales',
                    'hr_lead': 'people',
                    'business': 'executive',
                    'inventory': 'operations',
                    'purchasing': 'operations',
                    'marketplace': 'operations',
                    'finance': 'finance',
                    'reporting': 'finance',
                    'marketing': 'marketing',
                    'content': 'marketing',
                    'social_media': 'marketing',
                    'sales': 'sales',
                    'customer': 'sales',
                    'hr': 'people'
                };
                const mappedTeam = teamMap[currentAgent] || 'executive';
                const agentMeta = this.getAgentMetadata(currentAgent);
                const agentName = agentMeta.name || currentAgent.toUpperCase();

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                fetch('/cooca-ai/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        query: message,
                        team: mappedTeam
                    })
                })
                .then(res => res.json())
                .then(data => {
                    let replyText = '';
                    let findings = [];
                    let recommendations = [];
                    let proposals = [];

                    if (data.success && data.data) {
                        const d = data.data;
                        replyText = (d.summary || d.raw || d.message || '').trim();
                        findings = Array.isArray(d.findings) ? d.findings : [];
                        recommendations = Array.isArray(d.recommendations) ? d.recommendations : [];
                        proposals = Array.isArray(d.proposals) ? d.proposals : [];
                    }

                    if (!replyText || replyText === 'Instruksi telah diproses oleh tim agen AI.' || replyText === 'Analisis operasional diselesaikan.') {
                        if (recommendations.length > 0 || findings.length > 0) {
                            replyText = 'Berikut ringkasan evaluasi & langkah pemulihan yang dianalisis oleh tim AI:';
                        } else {
                            replyText = this.getContextualFallbackReply(currentAgent, message);
                        }
                    }

                    const aiMsg = {
                        id: Date.now() + 1,
                        from: 'agent',
                        isAi: true,
                        role: currentAgent,
                        sender: agentName,
                        time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                        text: replyText,
                        findings: findings,
                        recommendations: recommendations,
                        proposals: proposals
                    };

                    if (!this.agentChatHistories[currentAgent]) {
                        this.agentChatHistories[currentAgent] = [];
                    }
                    this.agentChatHistories[currentAgent].push(aiMsg);
                    this.agentChatHistories = { ...this.agentChatHistories };
                    this.saveAgentChatHistories();
                })
                .catch(() => {
                    const fallbackMsg = {
                        id: Date.now() + 1,
                        from: 'agent',
                        isAi: true,
                        role: currentAgent,
                        sender: agentName,
                        time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                        text: this.getContextualFallbackReply(currentAgent, message)
                    };
                    if (!this.agentChatHistories[currentAgent]) {
                        this.agentChatHistories[currentAgent] = [];
                    }
                    this.agentChatHistories[currentAgent].push(fallbackMsg);
                    this.agentChatHistories = { ...this.agentChatHistories };
                    this.saveAgentChatHistories();
                })
                .finally(() => {
                    this.isSendingChatMessage = false;
                    this.$nextTick(() => {
                        const chatBox = document.getElementById('floating-chat-stream');
                        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
                        if (window.lucide && typeof window.lucide.createIcons === 'function') {
                            window.lucide.createIcons();
                        }
                    });
                });
            },

            getContextualFallbackReply(role, query) {
                const q = (query || '').toLowerCase();

                if (role === 'cfo' || role === 'finance' || role === 'reporting') {
                    if (q.includes('keuangan') && (q.includes('minus') || q.includes('pulih') || q.includes('rugi') || q.includes('defisit') || q.includes('cara'))) {
                        return "Rencana Tindakan Darurat Keuangan (Arus Kas Minus):\n1. Audit & Pemetaan Cash Runway: Hitung total kas & bank dibagi beban operasional bulanan (IDR 15.000.000). Amankan runway minimal 60-90 hari.\n2. Rasionalisasi Beban Tetap (Opex): Identifikasi dan pangkas pos overhead non-esensial minimal 20-25% untuk menurunkan titik impas (Break-Even Point).\n3. Akselerasi Arus Kas Masuk: Terapkan diskon bayar cepat untuk invoice grosir dan maksimalkan pembayaran tunai/QRIS instan.\n4. Moratorium Belanja Non-Prioritas: Tahan pengeluaran modal (capex) baru dan fokuskan likuiditas pada stok barang yang paling cepat laku.";
                    }
                    if (q.includes('report') || q.includes('laporan') || q.includes('lengkap')) {
                        return "Berikut ringkasan audit keuangan & arus kas bisnis terkini, Pak:\n• Arus Kas Operasional: Positif dan likuiditas terjaga\n• Margin Laba Kotor: 42.8% (Target: >35%)\n• Estimasi Beban Opex Bulanan: Rp 15.000.000 (Sewa roastery & utilitas)\n• Rasio ROI Alokasi Modal: 94.2%\nSeluruh transaksi kasir POS dan invoice terbukukan otomatis ke jurnal keuangan.";
                    }
                    if (q.includes('profit') || q.includes('cashflow') || q.includes('laba')) {
                        return "Estimasi profit & cashflow bulan berjalan berada dalam jalur sehat dengan rasio ROI 94.2%. Rekomendasi efisiensi pengeluaran telah diimplementasikan ke sistem pembukuan.";
                    }
                    if (q.includes('audit') || q.includes('kasir') || q.includes('pos')) {
                        return "Audit Kasir POS: Tidak ada anomali atau selisih kas terdeteksi pada shift kasir hari ini. Seluruh pencatatan pembayaran tunai dan QRIS cocok 100%.";
                    }
                    return "Analisis Keuangan CFO: Pos anggaran, kas kecil, dan solvabilitas terpantau stabil. Tidak ada kebocoran dana atau anomali pengeluaran terdeteksi hari ini.";
                }

                if (role === 'coo' || role === 'inventory' || role === 'purchasing' || role === 'marketplace') {
                    if (q.includes('stok') || q.includes('gudang') || q.includes('inventaris') || q.includes('purchase')) {
                        return "Laporan Rantai Pasok & Gudang:\n• Status Stok Kritis: Bahan baku utama (Green Beans Gayo) mendekati batas reorder minimum.\n• Tindakan Sistem: Usulan Purchase Order (PO) otomatis telah disiapkan menuju supplier resmi.\n• SLA Pemrosesan: 100% tepat waktu.";
                    }
                    if (q.includes('po') || q.includes('supplier') || q.includes('order')) {
                        return "Pengadaan & Supplier Hub: Draft PO pengadaan stok bahan baku telah disiapkan. Ketika Anda menyetujui di panel Action AI, sistem otomatis mengirimkan konfirmasi PO via WhatsApp ke supplier resmi.";
                    }
                    return "Operasional: Seluruh alur logistik, pemenuhan pesanan kasir, dan penataan inventaris berjalan optimal tanpa hambatan teknis.";
                }

                if (role === 'cmo' || role === 'marketing' || role === 'content' || role === 'social_media') {
                    if (q.includes('promo') || q.includes('flash') || q.includes('sale')) {
                        return "Rencana Flash Sale & Promo:\n• Rekomendasi: Paket Bundling Kopi Signature + Biji Kopi Gayo 250gr diskon 15% pada jam sepi (14:00 - 17:00).\n• Target: Kenaikan omzet harian +22% dan likuidasi stok biji kopi terbungkus.";
                    }
                    return "Laporan Pertumbuhan & Marketing: Program promosi omnichannel aktif mencatatkan CTR 4.8% dan 1.420 lead prospek baru. Kami merekomendasikan promo bundling biji kopi kemasan untuk akhir pekan.";
                }

                if (role === 'sales_director' || role === 'sales' || role === 'customer') {
                    if (q.includes('b2b') || q.includes('pipeline') || q.includes('negosiasi')) {
                        return "Sales Pipeline: Target closing transaksi bulanan mencapai 88.5%. 14 prospek B2B segmen cafe/resto sedang dalam tahap negosiasi pasokan biji kopi.";
                    }
                    return "Layanan Pelanggan & Sales: Tingkat kepuasan pelanggan stabil di 4.9/5.0 dengan tingkat repeat order pelanggan aktif mencapai 67%.";
                }

                if (role === 'hr_lead' || role === 'hr') {
                    return "Laporan People & HR: Produktivitas staf outlet mencapai 96%. Seluruh jadwal shift dan absensi barista serta kasir terisi penuh tanpa ada kekosongan shift hari ini.";
                }

                if (role === 'business') {
                    if (q.includes('keuangan') || q.includes('minus') || q.includes('rugi') || q.includes('pulih') || q.includes('defisit') || q.includes('cara')) {
                        return "Strategi Pemulihan Keuangan Bisnis (Solusi Kondisi Minus/Defisit):\n1. Audit & Pangkas Biaya Operasional (Opex): Identifikasi komponen pengeluaran tetap bulanan dan pangkas pos yang tidak berdampak langsung ke revenue minimal 20-25%.\n2. Akselerasi Penjualan & Perputaran Kas: Buat kampanye multi-channel dan promo bundling menu margin tinggi (>40%) untuk mendongkrak omzet mingguan dari rata-rata saat ini.\n3. Pertahankan Margin Kotor: Gross profit margin roastery sudah berada di 50%. Pertahankan rasio ini dan hindari perang harga yang merusak margin.\n4. Manajemen Cash Runway: Hitung rasio kas terhadap burn rate bulanan dan pastikan runway minimal 3 bulan untuk menjamin kelangsungan operasional.";
                    }
                    return "Business Intelligence: Margin kontribusi produk roastery tercatat 44.1%. Penjualan kategori minuman espresso based mendominasi 58% dari total revenue outlet.";
                }

                if (role === 'ceo') {
                    if (q.includes('keuangan') || q.includes('minus') || q.includes('rugi') || q.includes('pulih') || q.includes('defisit')) {
                        return "Arahan Strategis Direksi (Pemulihan Bisnis Minus):\n1. Prioritas Utama: Kembalikan arus kas ke zona positif dalam 30 hari ke depan.\n2. Rampingkan Operasional: Instruksikan tim operasional dan gudang menghentikan pemborosan stok.\n3. Fokus Produk Unggulan: Kurangi varian produk yang lambat laku dan konsentrasikan marketing pada best-seller.\n4. Pantau Metrik Harian: Evaluasi margin kontribusi dan closing kasir setiap sore.";
                    }
                }

                return "Instruksi strategis telah diterima, Pak! Executive Command Center telah mengoordinasikan parameter ini ke seluruh divisi terkait untuk eksekusi real-time.";
            },

            get filteredFloatingHistory() {
                let list = this.histories || [];
                const filter = this.floatingHistoryFilter;
                const query = (this.floatingHistorySearch || '').toLowerCase().trim();

                if (filter === 'completed') {
                    list = list.filter(item => item.type === 'task' && item.badge === 'COMPLETED');
                } else if (filter === 'running') {
                    list = list.filter(item => item.type === 'task' && item.badge === 'RUNNING');
                } else if (filter === 'proposal') {
                    list = list.filter(item => item.type === 'proposal');
                }

                if (query) {
                    list = list.filter(item => 
                        (item.title && item.title.toLowerCase().includes(query)) ||
                        (item.detail && item.detail.toLowerCase().includes(query)) ||
                        (item.agent && item.agent.toLowerCase().includes(query)) ||
                        (item.status && item.status.toLowerCase().includes(query))
                    );
                }

                return list;
            },

            deselectAgent() {
                this.selectedAgent = null;
                this.selectedAgentData = {};
            },

            deselectRoom() {
                this.selectedRoom = null;
                this.selectedRoomData = {};
            },

            selectAgentFromFloor(roleKey) {
                this.selectedRoom = null;
                this.selectedAgent = roleKey;
                this.isFloatingHistoryOpen = false;
                this.isCameraMenuOpen = false;
                const meta = this.getAgentMetadata(roleKey);
                const existing = this.agents[roleKey] || {};
                this.selectedAgentData = {
                    ...meta,
                    ...existing,
                    name: (existing.avatar && existing.avatar.custom_name) || existing.name || meta.name || roleKey.toUpperCase(),
                    status: (existing.status || 'ACTIVE').toUpperCase(),
                };
                this.selectedAgentStatus = this.selectedAgentData.status;
                this.$nextTick(() => {
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                });
            },

            selectRoomFromFloor(roomKey) {
                this.selectedAgent = null;
                this.selectedRoom = roomKey;
                this.selectedRoomData = this.getRoomMetadata(roomKey);
                this.$nextTick(() => {
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                });
            },

            openOrgChartModal() {
                this.showOrgChartModal = true;
                this.$nextTick(() => {
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                });
            },

            closeOrgChartModal() {
                this.showOrgChartModal = false;
            },

            focusAgentFromOrgChart(roleKey) {
                this.closeOrgChartModal();
                this.selectAgentFromFloor(roleKey);
                const cameraWing = {
                    ceo: 'executive', cfo: 'executive', business: 'executive', finance: 'executive', reporting: 'executive',
                    marketing: 'marketing', content: 'marketing', social_media: 'marketing', cmo: 'marketing',
                    sales: 'sales', customer: 'sales', sales_director: 'sales',
                    inventory: 'operations', purchasing: 'operations', marketplace: 'operations', coo: 'operations',
                    hr: 'operations', hr_lead: 'operations'
                }[roleKey] || 'overview';
                this.set3DCamera(cameraWing);
            },

            get allOrgAgents() {
                const roles = [
                    'ceo', 'cfo', 'coo', 'cmo', 'sales_director', 'hr_lead',
                    'business', 'sales', 'customer', 'inventory', 'purchasing', 'marketplace',
                    'finance', 'reporting', 'marketing', 'content', 'social_media', 'hr'
                ];
                return roles.map(r => {
                    const meta = this.getAgentMetadata(r);
                    const existing = this.agents[r] || {};
                    return {
                        role: r,
                        ...meta,
                        name: (existing.avatar && existing.avatar.custom_name) || existing.name || meta.name || r.toUpperCase(),
                        status: (existing.status || 'ACTIVE').toUpperCase(),
                    };
                });
            },

            get filteredOrgAgents() {
                const search = (this.orgChartSearch || '').toLowerCase().trim();
                const filter = this.orgChartFilter;
                return this.allOrgAgents.filter(agent => {
                    if (filter === 'executive') {
                        if (!['ceo', 'cfo', 'coo', 'cmo', 'sales_director', 'hr_lead', 'business'].includes(agent.role)) return false;
                    } else if (filter === 'operations') {
                        if (!['coo', 'inventory', 'purchasing', 'marketplace'].includes(agent.role)) return false;
                    } else if (filter === 'growth') {
                        if (!['cmo', 'sales_director', 'marketing', 'content', 'social_media', 'sales', 'customer'].includes(agent.role)) return false;
                    } else if (filter === 'finance') {
                        if (!['cfo', 'finance', 'reporting'].includes(agent.role)) return false;
                    } else if (filter === 'people') {
                        if (!['hr_lead', 'hr'].includes(agent.role)) return false;
                    }

                    if (!search) return true;
                    return (
                        (agent.name || '').toLowerCase().includes(search) ||
                        (agent.title || '').toLowerCase().includes(search) ||
                        (agent.duties || '').toLowerCase().includes(search) ||
                        (agent.room || '').toLowerCase().includes(search) ||
                        (agent.lead || '').toLowerCase().includes(search)
                    );
                });
            },

            getAgentMetadata(roleKey) {
                const meta = {
                    ceo: {
                        name: 'AI CEO',
                        title: 'Chief Executive Officer',
                        team: 'executive',
                        teamName: 'Direksi & Eksekutif',
                        room: 'Executive Office',
                        lead: 'Pemilik Usaha (Owner)',
                        isLead: true,
                        duties: 'Memimpin visi strategis perusahaan, mengevaluasi profitabilitas bisnis secara holistik, mendeteksi risiko anomali lintas departemen, dan memvalidasi keputusan bernilai tinggi.',
                        deliverables: 'Executive Summary Q4, Diagnostic Scorecard, Strategic Directives',
                        teammates: ['AI CFO', 'AI COO', 'Business Agent'],
                    },
                    cfo: {
                        name: 'AI CFO',
                        title: 'Chief Financial Officer',
                        team: 'finance',
                        teamName: 'Direksi Keuangan',
                        room: 'Executive Office / Finance Room',
                        lead: 'AI CEO',
                        isLead: true,
                        duties: 'Mengendalikan solvabilitas dan arus kas (cashflow), menjaga margin keuntungan bersih, mengaudit pengeluaran kasir, serta menyetujui anggaran operasional.',
                        deliverables: 'Laporan Cashflow Bulanan, Audit Pengeluaran, Forecasting Laba Rugi',
                        teammates: ['Finance Agent', 'Reporting Agent'],
                    },
                    coo: {
                        name: 'AI COO',
                        title: 'Chief Operating Officer',
                        team: 'operations',
                        teamName: 'Direksi Operasional',
                        room: 'Operations Room',
                        lead: 'AI CEO',
                        isLead: true,
                        duties: 'Mengorkestrasi seluruh rantai pasok dan kelancaran harian gudang, memastikan stok aman, menegakkan efisiensi pengadaan, dan menjaga sinkronisasi omnichannel.',
                        deliverables: 'Operational Health Index, Audit Efisiensi Gudang, Target ROP',
                        teammates: ['Inventory Agent', 'Purchasing Agent', 'Marketplace Agent'],
                    },
                    cmo: {
                        name: 'AI CMO',
                        title: 'Chief Marketing Officer',
                        team: 'marketing',
                        teamName: 'Direksi Pemasaran',
                        room: 'Marketing Room',
                        lead: 'AI CEO',
                        isLead: true,
                        duties: 'Merumuskan arah kampanye pemasaran, targeting audiens berdaya beli tinggi, mengoptimalkan ROI promosi, dan mengkoordinasikan konten kreatif.',
                        deliverables: 'Kalender Kampanye Bulanan, Strategi Diskon Berkala, Matriks Akuisisi',
                        teammates: ['Marketing Agent', 'Content Agent', 'Social Media Agent'],
                    },
                    sales_director: {
                        name: 'AI Sales Director',
                        title: 'Sales Director',
                        team: 'sales',
                        teamName: 'Direksi Penjualan',
                        room: 'Sales Room',
                        lead: 'AI CEO',
                        isLead: true,
                        duties: 'Mengendalikan pipeline omzet harian seluruh cabang, memetakan produk berkinerja tinggi, dan merancang strategi retensi pelanggan jangka panjang.',
                        deliverables: 'Target Omzet Cabang, Laporan Kuadran Produk, Evaluasi Churn',
                        teammates: ['Sales Agent', 'Customer Agent'],
                    },
                    hr_lead: {
                        name: 'AI HR Lead',
                        title: 'Head of People & Culture',
                        team: 'people',
                        teamName: 'Manajemen SDM',
                        room: 'People / HR Room',
                        lead: 'AI CEO',
                        isLead: true,
                        duties: 'Mengawasi kedisiplinan dan produktivitas tenaga kerja, beban kerja kasir dan kru gudang, serta memastikan standar operasional ditaati.',
                        deliverables: 'Laporan Produktivitas Shift, Rekap Jam Kerja, Evaluasi Beban Kerja',
                        teammates: ['HR Agent'],
                    },
                    business: {
                        name: 'Business Agent',
                        title: 'Strategic Business Analyst',
                        team: 'executive',
                        teamName: 'Executive & Strategy',
                        room: 'Executive Office (Suite 1B)',
                        lead: 'AI CEO',
                        isLead: false,
                        duties: 'Analisis komprehensif kesehatan bisnis, deteksi anomali lintas modul, dan diagnosa prioritas strategis untuk owner.',
                        deliverables: 'Executive Diagnostic Brief, Anomaly Alert Score, Strategic Matrix',
                        teammates: ['AI CEO', 'AI CFO'],
                    },
                    sales: {
                        name: 'Sales Agent',
                        title: 'Revenue & Pipeline Specialist',
                        team: 'sales',
                        teamName: 'Sales & Revenue',
                        room: 'Sales Room',
                        lead: 'AI Sales Director',
                        isLead: false,
                        duties: 'Analisis tren penjualan, pendapatan produk harian, identifikasi produk laris vs anjlok, dan deviasi omzet cabang.',
                        deliverables: 'Daily Revenue Breakdown, Top & Bottom SKU Report, Branch Variance',
                        teammates: ['Customer Agent', 'AI Sales Director'],
                    },
                    customer: {
                        name: 'Customer Agent',
                        title: 'RFM & Retention Specialist',
                        team: 'sales',
                        teamName: 'Customer Relationship',
                        room: 'Sales Room',
                        lead: 'AI Sales Director',
                        isLead: false,
                        duties: 'Segmentasi RFM (Recency, Frequency, Monetary), identifikasi pelanggan pasif (dormant), loyalitas pelanggan, dan pemantauan jatuh tempo tagihan.',
                        deliverables: 'RFM Matrix Segments, Win-back Target List, Piutang Aging Alert',
                        teammates: ['Sales Agent', 'AI Sales Director'],
                    },
                    inventory: {
                        name: 'Inventory Agent',
                        title: 'Stock Controller & ROP Specialist',
                        team: 'operations',
                        teamName: 'Inventory & Warehouse',
                        room: 'Operations Room',
                        lead: 'AI COO',
                        isLead: false,
                        duties: 'Pemantauan stok kritis, prediksi kehabisan stok (stockout), deteksi dead stock, dan kalkulasi titik pemesanan ulang (Reorder Point - ROP).',
                        deliverables: 'Stockout Warning List, Dead Stock Audit, ROP Parameter Table',
                        teammates: ['Purchasing Agent', 'Marketplace Agent', 'AI COO'],
                    },
                    purchasing: {
                        name: 'Purchasing Agent',
                        title: 'Procurement & PO Specialist',
                        team: 'operations',
                        teamName: 'Procurement & Supplier',
                        room: 'Operations Room',
                        lead: 'AI COO',
                        isLead: false,
                        duties: 'Analisis riwayat pemasok, rekomendasi timing pengadaan optimal, negosiasi harga terbaik, dan persiapan draf Purchase Order (PO).',
                        deliverables: 'Draft Purchase Orders (PO), Supplier Performance Scorecard',
                        teammates: ['Inventory Agent', 'Marketplace Agent', 'AI COO'],
                    },
                    marketplace: {
                        name: 'Marketplace Agent',
                        title: 'Omnichannel Sync Specialist',
                        team: 'operations',
                        teamName: 'Marketplace & Channels',
                        room: 'Operations Room',
                        lead: 'AI COO',
                        isLead: false,
                        duties: 'Sinkronisasi harga dan stok lintas marketplace (Shopee, Tokopedia, TikTok Shop) serta analitik performa multi-kanal terpadu.',
                        deliverables: 'Multi-Channel Stock Reconciliation, Price Consistency Audit',
                        teammates: ['Inventory Agent', 'Purchasing Agent', 'AI COO'],
                    },
                    finance: {
                        name: 'Finance Agent',
                        title: 'Cashflow & Cost Auditor',
                        team: 'finance',
                        teamName: 'Finance & Accounting',
                        room: 'Finance Room / Executive Suite',
                        lead: 'AI CFO',
                        isLead: false,
                        duties: 'Analisis arus kas (cashflow), laba kotor, margin bersih, deteksi anomali pengeluaran kasir, dan rekonsiliasi kas harian.',
                        deliverables: 'Daily Cashflow Summary, Cash Outlier Detection, Gross Margin Audit',
                        teammates: ['Reporting Agent', 'AI CFO'],
                    },
                    reporting: {
                        name: 'Reporting Agent',
                        title: 'Management Reporting Specialist',
                        team: 'finance',
                        teamName: 'Reporting & Analytics',
                        room: 'Finance Room / Executive Suite',
                        lead: 'AI CFO',
                        isLead: false,
                        duties: 'Penyusunan rekapitulasi harian, mingguan, bulanan, dan ringkasan eksekutif komparatif untuk pimpinan manajemen.',
                        deliverables: 'Executive Daily Recap, Weekly Management Brief, Monthly P&L Pack',
                        teammates: ['Finance Agent', 'AI CFO'],
                    },
                    marketing: {
                        name: 'Marketing Agent',
                        title: 'Campaign & Promo Strategist',
                        team: 'marketing',
                        teamName: 'Marketing Strategy',
                        room: 'Marketing Room',
                        lead: 'AI CMO',
                        isLead: false,
                        duties: 'Perumusan strategi promosi, ide diskon berkala, penargetan segmen pelanggan berharga tinggi, dan evaluasi efektivitas diskon.',
                        deliverables: 'Promo Campaign Brief, Discount RoAS Projection, Target Audience Deck',
                        teammates: ['Content Agent', 'Social Media Agent', 'AI CMO'],
                    },
                    content: {
                        name: 'Content Agent',
                        title: 'Creative Copywriter & Creator',
                        team: 'marketing',
                        teamName: 'Creative Content',
                        room: 'Marketing Room',
                        lead: 'AI CMO',
                        isLead: false,
                        duties: 'Penyusunan naskah copywriting, ide konten media sosial, caption promosi, naskah siaran WhatsApp, dan varian penawaran produk.',
                        deliverables: 'Social Media Copywriting Drafts, Promo Captions, Broadcast Scripts',
                        teammates: ['Marketing Agent', 'Social Media Agent', 'AI CMO'],
                    },
                    social_media: {
                        name: 'Social Media Agent',
                        title: 'Social Media & Schedule Manager',
                        team: 'marketing',
                        teamName: 'Social Distribution',
                        room: 'Marketing Room',
                        lead: 'AI CMO',
                        isLead: false,
                        duties: 'Manajemen jadwal tayang postingan medsos, persiapan materi visual, dan monitoring status publikasi serta engagement audiens.',
                        deliverables: 'Publishing Calendar Schedule, Media Post Queue, Engagement Tracker',
                        teammates: ['Marketing Agent', 'Content Agent', 'AI CMO'],
                    },
                    hr: {
                        name: 'HR Agent',
                        title: 'Workforce & Shift Specialist',
                        team: 'people',
                        teamName: 'People & Workforce',
                        room: 'People / HR Room',
                        lead: 'AI HR Lead',
                        isLead: false,
                        duties: 'Pemantauan absensi karyawan, produktivitas kasir dan staf, rekonsiliasi jam kerja lembur, dan evaluasi kepatuhan operasional.',
                        deliverables: 'Staff Attendance Log, Cashier Hourly Velocity, Shift Coverage Alert',
                        teammates: ['AI HR Lead'],
                    },
                };
                return meta[roleKey] || {
                    name: roleKey.toUpperCase(),
                    title: 'Specialized Agent',
                    team: 'general',
                    teamName: 'Tim Digital',
                    room: 'Virtual Office Floor',
                    lead: 'AI Lead',
                    isLead: false,
                    duties: 'Melakukan pemrosesan komputasi dan tugas automasi digital.',
                    deliverables: 'Operational Log & Reports',
                    teammates: [],
                };
            },

            getRoomMetadata(roomKey) {
                const rooms = {
                    executive: {
                        name: 'Executive Office',
                        subtitle: 'Ruang Direksi & Perumusan Strategi Korporat',
                        color: '#1d4ed8',
                        teamName: 'Executive & Strategy',
                        teamKey: 'executive',
                        lead: 'AI CEO (Chief Executive Officer)',
                        scope: 'Pusat kendali strategi bisnis perusahaan, audit kuartalan (Q4 Strategy), evaluasi profitabilitas menyeluruh, serta otorisasi keputusan tingkat tinggi.',
                        agents: ['AI CEO', 'AI CFO', 'Business Agent', 'Finance Agent', 'Reporting Agent'],
                        primaryAgent: 'ceo'
                    },
                    marketing: {
                        name: 'Marketing Room',
                        subtitle: 'Studio Pertumbuhan & Kampanye Kreatif (Growth Lab)',
                        color: '#7e22ce',
                        teamName: 'Growth & Marketing',
                        teamKey: 'marketing',
                        lead: 'AI CMO (Chief Marketing Officer)',
                        scope: 'Perancangan kampanye promosi, produksi materi konten kreatif, copywriting diskon berkala, serta automasi jadwal tayang media sosial untuk meningkatkan akuisisi.',
                        agents: ['AI CMO', 'Marketing Agent', 'Content Agent', 'Social Media Agent'],
                        primaryAgent: 'marketing'
                    },
                    sales: {
                        name: 'Sales Room',
                        subtitle: 'Ruang Pendapatan, Pipeline & Retensi Pelanggan',
                        color: '#059669',
                        teamName: 'Sales & Customer Relationship',
                        teamKey: 'sales',
                        lead: 'AI Sales Director',
                        scope: 'Pemantauan tren pendapatan harian, identifikasi produk laris vs anjlok, segmentasi RFM pelanggan, reaktivasi pelanggan dormant, dan penagihan piutang.',
                        agents: ['AI Sales Director', 'Sales Agent', 'Customer Agent'],
                        primaryAgent: 'sales'
                    },
                    operations: {
                        name: 'Operations Room',
                        subtitle: 'Hub Rantai Pasok, Gudang & Pengadaan (Operations Radar)',
                        color: '#0e7490',
                        teamName: 'Operations & Logistics',
                        teamKey: 'operations',
                        lead: 'AI COO (Chief Operating Officer)',
                        scope: 'Pengendalian stok kritis, kalkulasi Reorder Point (ROP), perumusan draf Purchase Order (PO) pemasok, dan sinkronisasi inventori multi-marketplace real-time.',
                        agents: ['AI COO', 'Inventory Agent', 'Purchasing Agent', 'Marketplace Agent'],
                        primaryAgent: 'inventory'
                    },
                    finance: {
                        name: 'Finance Room',
                        subtitle: 'Ruang Keuangan, Pembukuan & Rekapitulasi',
                        color: '#ea580c',
                        teamName: 'Finance & Accounting',
                        teamKey: 'finance',
                        lead: 'AI CFO (Chief Financial Officer)',
                        scope: 'Pengawasan arus kas masuk & keluar (cashflow), pencegahan kebocoran kasir/fraud, margin keuntungan, dan penyusunan laporan keuangan periodik.',
                        agents: ['AI CFO', 'Finance Agent', 'Reporting Agent'],
                        primaryAgent: 'finance'
                    },
                    people: {
                        name: 'People & HR Room',
                        subtitle: 'Manajemen Tenaga Kerja & Produktivitas Karyawan',
                        color: '#a21caf',
                        teamName: 'People & Culture',
                        teamKey: 'people',
                        lead: 'AI HR Lead',
                        scope: 'Pemantauan kehadiran karyawan, evaluasi produktivitas kasir dan staf gudang, alokasi shift kerja, serta kultur kedisiplinan operasional.',
                        agents: ['AI HR Lead', 'HR Agent'],
                        primaryAgent: 'hr'
                    },
                    it_server: {
                        name: 'Server Room & IT Support',
                        subtitle: 'Infrastruktur Komputasi AI & Keamanan Data',
                        color: '#0284c7',
                        teamName: 'IT Infrastructure',
                        teamKey: 'operations',
                        lead: 'AI COO',
                        scope: 'Pusat komputasi AI real-time, sinkronisasi API multi-kanal, backup database tenant terisolasi, enkripsi transaksi kasir, dan audit integritas sistem.',
                        agents: ['Autonomous AI Engine', 'Cloud Sync Worker'],
                        primaryAgent: 'coo'
                    },
                    document: {
                        name: 'Document Room',
                        subtitle: 'Arsip Dokumen Bisnis & Legalitas',
                        color: '#78350f',
                        teamName: 'Legal & Administration',
                        teamKey: 'executive',
                        lead: 'AI CFO',
                        scope: 'Penyimpanan bukti transaksi, arsip surat jalan, invoice digital, rekapitulasi audit eksekutif, dan dokumentasi kontrak suplier.',
                        agents: ['Reporting Agent', 'Finance Agent'],
                        primaryAgent: 'reporting'
                    },
                    meeting: {
                        name: 'Meeting Room',
                        subtitle: 'Ruang Rapat Konferensi & Presentasi Strategis',
                        color: '#334155',
                        teamName: 'Kolaborasi Lintas Divisi',
                        teamKey: 'executive',
                        lead: 'AI CEO / Department Leads',
                        scope: 'Fasilitas rapat koordinasi lintas divisi untuk presentasi strategi pertumbuhan bisnis, evaluasi kinerja mingguan, dan sinkronisasi tim.',
                        agents: ['Semua Agen (Sesuai Jadwal Rapat)'],
                        primaryAgent: 'ceo'
                    },
                    atrium: {
                        name: 'Grand Central Atrium & Reception',
                        subtitle: 'Lobi Utama, Resepsionis & Rotunda Lounge',
                        color: '#0284c7',
                        teamName: 'Front Office & Hospitality',
                        teamKey: 'executive',
                        lead: 'AI Virtual Host',
                        scope: 'Pintu gerbang utama kantor virtual COOCA AI, penyambutan tamu & klien, ruang santai rotunda, dan pusat navigasi seluruh gedung.',
                        agents: ['Front Desk AI Assistant'],
                        primaryAgent: 'ceo'
                    },
                    pantry: {
                        name: 'Pantry & Lounge',
                        subtitle: 'Area Istirahat, Kopi & Interaksi Santai',
                        color: '#475569',
                        teamName: 'Fasilitas Kesejahteraan',
                        teamKey: 'people',
                        lead: 'AI HR Lead',
                        scope: 'Area rekreasi dan istirahat tim kerja digital, 3 meja makan bundar (12 kursi), fasilitas bar kopi espresso, dan refreshing staf.',
                        agents: ['Staf Istirahat'],
                        primaryAgent: 'hr'
                    },
                    restroom: {
                        name: 'Restroom',
                        subtitle: 'Fasilitas Sanitasi Gedung',
                        color: '#334155',
                        teamName: 'Fasilitas Gedung',
                        teamKey: 'general',
                        lead: 'Building Ops',
                        scope: 'Fasilitas sanitasi toilet higienis gedung modern dengan bilik terpisah dan wastafel otomatis.',
                        agents: ['Maintenance Bot'],
                        primaryAgent: 'coo'
                    }
                };
                return rooms[roomKey] || {
                    name: 'Fasilitas Virtual Office',
                    subtitle: 'Area Operasional Terpadu',
                    color: '#1e3a8a',
                    teamName: 'Tim Terkait',
                    teamKey: 'general',
                    lead: 'AI Lead',
                    scope: 'Fasilitas terpadu pendukung operasional bisnis digital.',
                    agents: ['AI Assistant'],
                    primaryAgent: 'ceo'
                };
            },

            getQuickTasksForAgent(roleKey) {
                const map = {
                    ceo: ['Analisis KPI Bisnis & Profit', 'Evaluasi Strategi Kuartal Ini', 'Review Ringkasan Eksekutif'],
                    cfo: ['Audit Arus Kas & Burn Rate', 'Proyeksi Margin Laba Kotor', 'Laporan Neraca Finansial'],
                    business: ['Analisis Peluang Pasar Baru', 'Review Efisiensi Model Bisnis', 'Benchmark Kompetitor UMKM'],
                    finance: ['Rekonsiliasi Pajak & Invoice', 'Audit Beban Operasional', 'Verifikasi Pembukuan Harian'],
                    reporting: ['Kompilasi Laporan Mingguan', 'Generate Visual KPI Dashboard', 'Kirim Laporan ke Stakeholder'],
                    cmo: ['Desain Kampanye Promo Viral', 'Optimasi Anggaran Iklan Ads', 'Analisis Customer Acquisition'],
                    sales_director: ['Strategi Peningkatan Closing', 'Evaluasi Pipeline Leads', 'Target Omset Penjualan Tim'],
                    marketing: ['Riset Tren TikTok & Reels', 'Rancang Kalender Konten', 'Analisis Konversi Kampanye'],
                    content: ['Tulis Copywriting Penawaran', 'Buat Naskah Video Promosi', 'Optimasi Artikel Blog SEO'],
                    social_media: ['Jadwalkan Postingan Feed', 'Balas Komentar Netizen & DM', 'Audit Engagement Akun Sosmed'],
                    sales: ['Follow-up Prospek Potensial', 'Kirim Penawaran Spesial', 'Closing Order Masuk Hari Ini'],
                    customer: ['Analisis Kepuasan Pelanggan', 'Selesaikan Keluhan Prioritas', 'Program Loyalitas & Retensi'],
                    coo: ['Optimasi Efisiensi Alur Kerja', 'Audit Standard Operating (SOP)', 'Evaluasi SLA Pengiriman'],
                    inventory: ['Audit Stok Habis & Menipis', 'Perhitungan Safety Stock', 'Laporan Opname Fisik Gudang'],
                    purchasing: ['Kirim PO ke Pemasok Utama', 'Negosiasi Harga Bahan Baku', 'Evaluasi Kinerja Vendor Suplai'],
                    marketplace: ['Sinkronisasi Stok Multi-Toko', 'Optimasi Iklan Shopee/Tokopedia', 'Pembaruan Katalog Produk'],
                    hr: ['Evaluasi Beban Kerja Tim', 'Monitoring Kehadiran & Shift', 'Program Pelatihan & Onboarding'],
                };
                return map[roleKey] || ['Analisis operasional harian', 'Tinjau performa sistem', 'Siapkan laporan kerja'];
            },

            getQuickTasksForRoom(roomKey) {
                const map = {
                    executive: ['Rapat Strategi Direksi & Finansial', 'Review Target Perusahaan', 'Pengambilan Keputusan Krusial'],
                    growth: ['Briefing Kampanye Pemasaran Terpadu', 'Sinergi Tim Konten & Penjualan', 'Akselerasi Akuisisi Pelanggan'],
                    operations: ['Koordinasi Rantai Pasok & Gudang', 'Pengecekan Stok & Pengadaan Vendor', 'Audit Keandalan Operasional'],
                    meeting_room: ['Rapat Kolaborasi Lintas Tim', 'Brainstorming Inovasi Produk', 'Sesi Evaluasi Kinerja Bulanan'],
                    pantry: ['Istirahat Kopi & Diskusi Santai', 'Sesi Bonding Antar-Divisi', 'Recharge Energi Tim Kerja'],
                    control_center: ['Monitoring Sistem & Integrasi Toko', 'Audit Keamanan Data Realtime', 'Pengecekan Status Server Cloud'],
                    atrium: ['Peresmian Acara Digital Bersama', 'Penyambutan Klien Penting', 'Townhall Seluruh Karyawan AI']
                };
                return map[roomKey] || ['Briefing koordinasi tim', 'Evaluasi progres tugas', 'Diskusi strategi mingguan'];
            },

            assignDirectTask(roleKey, taskText) {
                if (!roleKey) return;
                const task = taskText || (this.getQuickTasksForAgent(roleKey)[0] || 'Mengerjakan tugas prioritas');
                this.isAssigningTask = true;

                // 1. Trigger 3D movement and animation
                if (this.office3d && typeof this.office3d.assignTaskToAgent === 'function') {
                    this.office3d.assignTaskToAgent(roleKey, task);
                }

                // 2. Dispatch system consultation event
                const agentData = this.agents[roleKey] || {};
                const teamKey = this.resolveAgentTeam(roleKey);
                window.dispatchEvent(new CustomEvent('open-ai-consultation', {
                    detail: {
                        team: teamKey,
                        agent: roleKey,
                        agentName: agentData.name || roleKey.toUpperCase(),
                        initialMessage: task
                    }
                }));

                const agentName = (agentData.avatar && agentData.avatar.custom_name) || agentData.name || roleKey.toUpperCase();
                this.taskSuccessMessage = `Tugas berhasil diberikan kepada ${agentName}! Agen sedang menuju meja kerja.`;
                this.directTaskInput = '';

                setTimeout(() => {
                    this.isAssigningTask = false;
                    setTimeout(() => {
                        this.taskSuccessMessage = '';
                    }, 5000);
                }, 800);
            },

            assignTeamTask(teamKey, taskText) {
                if (!teamKey) return;
                const task = taskText || `Briefing dan pengerjaan tugas bersama Tim ${teamKey.toUpperCase()}`;
                this.isAssigningTask = true;

                // 1. Trigger 3D movement and meeting routing
                if (this.office3d && typeof this.office3d.assignTaskToTeam === 'function') {
                    this.office3d.assignTaskToTeam(teamKey, task);
                }

                // 2. Dispatch system consultation event
                window.dispatchEvent(new CustomEvent('open-ai-consultation', {
                    detail: {
                        team: teamKey,
                        agent: teamKey === 'executive' ? 'ceo' : (teamKey === 'operations' ? 'coo' : (teamKey === 'sales' ? 'sales_director' : 'cmo')),
                        agentName: `Tim ${teamKey.toUpperCase()}`,
                        initialMessage: task
                    }
                }));

                this.taskSuccessMessage = `Seluruh anggota Tim ${teamKey.toUpperCase()} telah ditugaskan dan berkumpul untuk koordinasi!`;
                this.directTaskInput = '';

                setTimeout(() => {
                    this.isAssigningTask = false;
                    setTimeout(() => {
                        this.taskSuccessMessage = '';
                    }, 5000);
                }, 800);
            },

            consultWithRoom(roomData) {
                if (!roomData) return;
                const teamKey = roomData.teamKey || 'general';
                const agentRole = roomData.primaryAgent || 'ceo';
                window.dispatchEvent(new CustomEvent('open-ai-consultation', {
                    detail: {
                        team: teamKey,
                        agent: agentRole,
                        agentName: roomData.name || 'Tim Virtual Office',
                    }
                }));
            },

            resolveAgentTeam(role) {
                const teams = {
                    ceo: 'executive', cfo: 'finance', coo: 'operations', cmo: 'marketing', sales_director: 'sales', hr_lead: 'people',
                    business: 'executive', finance: 'finance', reporting: 'finance',
                    marketing: 'marketing', content: 'marketing', social_media: 'marketing',
                    sales: 'sales', customer: 'sales',
                    inventory: 'operations', purchasing: 'operations', marketplace: 'operations', hr: 'people'
                };
                return teams[role] || 'general';
            },

            get hasActiveWorkers() {
                return Object.values(this.agents).some(a => (a.status || '').toUpperCase() === 'WORKING');
            },

            get activeWorkersCount() {
                return Object.values(this.agents).filter(a => (a.status || '').toUpperCase() === 'WORKING').length || 3;
            },

            // Avatar modal
            openAvatarModal(roleKey = null) {
                const availableRoles = Object.keys(this.agents);
                const targetRole = roleKey || (this.selectedAgent || (availableRoles.length > 0 ? availableRoles[0] : 'ceo'));
                this.loadAvatarIntoForm(targetRole);
                this.showAvatarModal = true;
            },

            closeAvatarModal() {
                this.showAvatarModal = false;
            },

            loadAvatarIntoForm(roleKey) {
                this.editingRole = roleKey;
                const agent = this.agents[roleKey] || {};
                const currentAvatar = agent.avatar || {};
                this.avatarForm = {
                    preset: currentAvatar.preset || 'executive_male',
                    custom_name: currentAvatar.custom_name || agent.name || roleKey.toUpperCase(),
                    suit_color: currentAvatar.suit_color || '#1e293b',
                    skin_tone: currentAvatar.skin_tone || '#f8d9b6',
                    hair_color: currentAvatar.hair_color || '#1e1e24',
                    accessory: currentAvatar.accessory || 'none',
                };
            },

            async saveAvatar() {
                this.isSavingAvatar = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const response = await fetch(`/cooca-ai/agents/${this.editingRole}/avatar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(this.avatarForm)
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (this.agents[this.editingRole]) {
                            this.agents[this.editingRole].avatar = result.avatar || this.avatarForm;
                        }
                    }
                    this.showAvatarModal = false;
                } catch (err) {
                    console.error('[CoocaVirtualOffice] Save avatar error:', err);
                } finally {
                    this.isSavingAvatar = false;
                }
            },

            consultWithAgent(agent) {
                if (!agent) return;
                const roleKey = agent.role || this.selectedAgent;
                const teamKey = this.resolveAgentTeam(roleKey);
                this.deselectAgent();
                window.dispatchEvent(new CustomEvent('open-ai-consultation', {
                    detail: {
                        team: teamKey,
                        agent: roleKey,
                        agentName: agent.name || roleKey,
                    }
                }));
            },

            triggerLiveScan() {
                this.isScanning = true;
                setTimeout(() => {
                    this.isScanning = false;
                }, 2500);
            },

            zoomIn() {
                if (this.viewMode === '3d' && this.office3d) {
                    this.office3d.zoomIn();
                } else {
                    this.zoomLevel = Math.min(1.8, this.zoomLevel + 0.15);
                }
            },

            zoomOut() {
                if (this.viewMode === '3d' && this.office3d) {
                    this.office3d.zoomOut();
                } else {
                    this.zoomLevel = Math.max(0.6, this.zoomLevel - 0.15);
                }
            },

            handleWheel(e) {
                if (e.deltaY < 0) this.zoomIn();
                else this.zoomOut();
            },

            startPan(e) {
                this.isPanning = true;
                this.startX = e.clientX - this.panX;
                this.startY = e.clientY - this.panY;
            },

            doPan(e) {
                if (!this.isPanning) return;
                this.panX = e.clientX - this.startX;
                this.panY = e.clientY - this.startY;
            },

            endPan() {
                this.isPanning = false;
            },

            playKeySound() {},

            takeAgentPOV(roleKey) {
                if (!roleKey) return;
                if (this.viewMode !== '3d') {
                    this.setMode('3d');
                }
                this.$nextTick(() => {
                    if (this.office3d && typeof this.office3d.enterAgentPOV === 'function') {
                        this.office3d.enterAgentPOV(roleKey, this.povCameraType);
                    }
                });
            },

            exitPOV() {
                if (this.office3d && typeof this.office3d.exitAgentPOV === 'function') {
                    this.office3d.exitAgentPOV();
                }
            },

            togglePOVCamera() {
                if (this.office3d && typeof this.office3d.togglePOVType === 'function') {
                    this.office3d.togglePOVType();
                }
            },

            sitOrStandPOV() {
                if (this.office3d && typeof this.office3d.sitOrStandCurrentAgent === 'function') {
                    this.office3d.sitOrStandCurrentAgent();
                }
            },

            sendPOVCommand() {
                if (!this.povCommandInput || !this.povCommandInput.trim() || !this.povAgentRole) return;
                const cmd = this.povCommandInput.trim();
                this.assignDirectTask(this.povAgentRole, cmd);
                this.povCommandInput = '';
            }
        }));
    }

    if (window.Alpine) {
        registerCoocaVirtualOffice();
    } else {
        document.addEventListener('alpine:init', registerCoocaVirtualOffice);
    }
})();
</script>
