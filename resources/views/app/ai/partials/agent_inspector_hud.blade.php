{{--
    COOCA AI — Unified Interactive Inspector HUD (Agent & Room Details)
    Positioned over 3D WebGL Canvas & 2D Blueprint with Fullscreen Support
--}}
<div
    x-show="selectedAgent || selectedRoom"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    @click.stop
    @mousedown.stop
    @touchstart.stop
    class="absolute bottom-20 right-4 sm:right-6 z-50 max-w-sm sm:max-w-md w-full p-5 rounded-3xl bg-white/95 dark:bg-zinc-950/95 backdrop-blur-2xl border border-black/10 dark:border-white/20 text-slate-800 dark:text-white shadow-2xl space-y-4 select-none transition-colors pointer-events-auto max-h-[calc(100vh-6.5rem)] overflow-y-auto"
    style="display: none;"
>
    <!-- A. AGENT INSPECTION MODE -->
    <template x-if="selectedAgent">
        <div class="space-y-3.5">
            <!-- Top Header -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 via-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-lg shadow-lg shadow-cyan-500/25 relative ring-2 ring-white/20 shrink-0">
                        <span x-text="selectedAgentData.name ? selectedAgentData.name.charAt(0) : 'A'"></span>
                        <div class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-400 border border-white flex items-center justify-center text-[8px] animate-pulse">
                            <i data-lucide="sparkles" class="w-2.5 h-2.5 text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <span x-text="selectedAgentData.name || selectedAgent"></span>
                            <span class="text-[9px] font-mono px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                                  :class="selectedAgentStatus === 'WORKING' ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30' : (selectedAgentStatus === 'WAITING_APPROVAL' ? 'bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30' : 'bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-500/30')"
                                  x-text="'● ' + selectedAgentStatus"></span>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-300 font-medium" x-text="selectedAgentData.title || 'Specialized Agent'"></div>
                    </div>
                </div>
                <button @click="deselectAgent()" class="text-slate-400 hover:text-slate-900 dark:hover:text-white transition p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/10 cursor-pointer" title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Room Location & Team Badge -->
            <div class="flex items-center justify-between text-xs px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10">
                <span class="text-slate-500 dark:text-slate-400">Ruangan &amp; Tim:</span>
                <span class="font-semibold text-cyan-700 dark:text-cyan-300" x-text="(selectedAgentData.room || 'Virtual Office') + ' • ' + (selectedAgentData.teamName || 'Tim Digital')"></span>
            </div>

            <!-- Pembagian Tugas & Tanggung Jawab -->
            <div class="space-y-1 p-3.5 rounded-2xl bg-slate-100/80 dark:bg-black/40 border border-slate-200 dark:border-white/10">
                <div class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-cyan-500"></i>
                    <span>Tanggung Jawab Utama:</span>
                </div>
                <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-normal" x-text="selectedAgentData.duties || selectedAgentData.description || 'Melakukan tugas komputasi dan analisis operasional.'"></p>
            </div>

            <!-- Executive Lead & Collaborators -->
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 rounded-xl bg-slate-100/70 dark:bg-white/5 border border-slate-200/80 dark:border-white/5">
                    <span class="text-slate-500 dark:text-slate-400 block text-[10px] font-medium">Pimpinan / Lead:</span>
                    <span class="font-bold text-amber-700 dark:text-amber-300 truncate block mt-0.5" x-text="selectedAgentData.lead || 'AI Lead'"></span>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-100/70 dark:bg-white/5 border border-slate-200/80 dark:border-white/5">
                    <span class="text-slate-500 dark:text-slate-400 block text-[10px] font-medium">Rekan Kerja Tim:</span>
                    <span class="font-bold text-cyan-700 dark:text-cyan-300 truncate block mt-0.5" x-text="(selectedAgentData.teammates && selectedAgentData.teammates.length > 0) ? selectedAgentData.teammates.join(', ') : 'Mandiri'"></span>
                </div>
            </div>

            <!-- PRIMARY ACTION: Single Unified Consultation Modal Trigger -->
            <div class="pt-1 space-y-2">
                <button
                    type="button"
                    @click="consultWithAgent(selectedAgentData)"
                    class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-bold flex items-center justify-center gap-2 transition shadow-lg shadow-indigo-500/25 cursor-pointer active:scale-98"
                >
                    <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                    <span x-text="`Konsultasi / Tugaskan ${selectedAgentData.name || selectedAgent}`"></span>
                </button>

                <!-- Secondary Actions (POV Mode & Avatar Customizer) -->
                <div class="grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        @click="takeAgentPOV(selectedAgent)"
                        class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-800 dark:text-slate-200 text-xs font-semibold flex items-center justify-center gap-1.5 transition cursor-pointer"
                        title="Kendalikan agen dalam sudut pandang 3D"
                    >
                        <i data-lucide="gamepad-2" class="w-3.5 h-3.5 text-indigo-500"></i>
                        <span>POV Kamera</span>
                    </button>
                    <button
                        type="button"
                        @click="openAvatarModal(selectedAgent)"
                        class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-800 dark:text-slate-200 text-xs font-semibold flex items-center justify-center gap-1.5 transition cursor-pointer"
                        title="Ubah penampilan avatar karakter ini"
                    >
                        <i data-lucide="palette" class="w-3.5 h-3.5 text-purple-500"></i>
                        <span>Ganti Avatar</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- B. ROOM / FACILITY INSPECTION MODE -->
    <template x-if="selectedRoom && !selectedAgent">
        <div class="space-y-3.5">
            <!-- Top Header -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-white text-xl shadow-lg ring-2 ring-white/20 shrink-0"
                         :style="`background-color: ${selectedRoomData.color || '#1e3a8a'};`">
                        <i data-lucide="building" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <span x-text="selectedRoomData.name || selectedRoom"></span>
                        </div>
                        <div class="text-xs text-cyan-700 dark:text-cyan-300 font-medium" x-text="selectedRoomData.subtitle || 'Fasilitas Virtual Office'"></div>
                    </div>
                </div>
                <button @click="deselectRoom()" class="text-slate-400 hover:text-slate-900 dark:hover:text-white transition p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/10 cursor-pointer" title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Team & Supervisor Tag -->
            <div class="flex items-center justify-between text-xs px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10">
                <span class="text-slate-500 dark:text-slate-400">Tim &amp; Pimpinan:</span>
                <span class="font-semibold text-amber-700 dark:text-amber-300" x-text="(selectedRoomData.teamName || 'Tim Terkait') + ' (Lead: ' + (selectedRoomData.lead || 'AI Lead') + ')'"></span>
            </div>

            <!-- Scope & Lingkup Kerja Ruangan -->
            <div class="space-y-1 p-3.5 rounded-2xl bg-slate-100/80 dark:bg-black/40 border border-slate-200 dark:border-white/10">
                <div class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="target" class="w-3.5 h-3.5 text-cyan-500"></i>
                    <span>Lingkup Operasional:</span>
                </div>
                <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-normal" x-text="selectedRoomData.scope || 'Fasilitas operasional bisnis terpadu.'"></p>
            </div>

            <!-- Roster Agen di Ruangan Ini -->
            <div class="p-2.5 rounded-xl bg-slate-100/70 dark:bg-white/5 border border-slate-200/80 dark:border-white/5 space-y-1">
                <span class="text-slate-500 dark:text-slate-400 text-[10px] font-medium block">Agen di Ruangan Ini:</span>
                <div class="flex flex-wrap gap-1">
                    <template x-for="ag in (selectedRoomData.agents || [])" :key="ag">
                        <span class="px-2 py-0.5 rounded-md bg-white dark:bg-white/10 text-slate-800 dark:text-white border border-slate-200 dark:border-transparent text-[10px] font-mono" x-text="ag"></span>
                    </template>
                </div>
            </div>

            <!-- PRIMARY ACTION: Consult with Room Team -->
            <div class="pt-1">
                <button
                    type="button"
                    @click="consultWithRoom(selectedRoomData)"
                    class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-cyan-600 via-blue-600 to-indigo-600 hover:from-cyan-700 hover:to-indigo-700 text-white text-xs font-bold flex items-center justify-center gap-2 transition shadow-lg shadow-cyan-500/25 cursor-pointer active:scale-98"
                >
                    <i data-lucide="users" class="w-4 h-4 text-white"></i>
                    <span x-text="`Konsultasi dengan Tim ${selectedRoomData.name || 'Ruangan Ini'}`"></span>
                </button>
            </div>
        </div>
    </template>
</div>
