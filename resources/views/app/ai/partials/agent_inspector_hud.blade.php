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
    class="absolute bottom-20 right-4 sm:right-6 z-50 max-w-sm sm:max-w-md w-full p-4 rounded-3xl bg-white/95 dark:bg-zinc-950/95 backdrop-blur-2xl border border-black/10 dark:border-white/20 text-slate-800 dark:text-white shadow-2xl space-y-3.5 select-none transition-colors pointer-events-auto max-h-[calc(100vh-6.5rem)] overflow-y-auto"
    style="display: none;"
>
    <!-- A. AGENT INSPECTION MODE -->
    <template x-if="selectedAgent">
        <div class="space-y-3">
            <!-- Top Header -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 via-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-lg shadow-lg shadow-cyan-500/25 relative ring-2 ring-white/20">
                        <span x-text="selectedAgentData.name ? selectedAgentData.name.charAt(0) : 'A'"></span>
                        <div class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-400 border border-white flex items-center justify-center text-[8px] animate-pulse">
                            💎
                        </div>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <span x-text="selectedAgentData.name || selectedAgent"></span>
                            <span class="text-[9px] font-mono px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                                  :class="selectedAgentStatus === 'WORKING' ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30' : (selectedAgentStatus === 'WAITING_APPROVAL' ? 'bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30' : 'bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-500/30')"
                                  x-text="'● ' + selectedAgentStatus"></span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-300 font-medium" x-text="selectedAgentData.title || 'Specialized Agent'"></div>
                    </div>
                </div>
                <button @click="deselectAgent()" class="text-slate-400 hover:text-slate-900 dark:hover:text-white transition p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-white/10 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Room Location & Team Badge -->
            <div class="flex items-center justify-between text-[11px] px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10">
                <span class="text-slate-500 dark:text-slate-400">Ruangan &amp; Tim:</span>
                <span class="font-semibold text-cyan-700 dark:text-cyan-300" x-text="(selectedAgentData.room || 'Virtual Office') + ' • ' + (selectedAgentData.teamName || 'Tim Digital')"></span>
            </div>

            <!-- Pembagian Tugas & Tanggung Jawab -->
            <div class="space-y-1 p-3 rounded-2xl bg-slate-100/80 dark:bg-black/40 border border-slate-200 dark:border-white/10">
                <div class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-cyan-500"></i>
                    <span>Pembagian Tugas &amp; Tanggung Jawab:</span>
                </div>
                <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-normal" x-text="selectedAgentData.duties || selectedAgentData.description || 'Melakukan tugas komputasi dan analisis operasional.'"></p>
            </div>

            <!-- Executive Lead & Collaborators -->
            <div class="grid grid-cols-2 gap-2 text-[10px]">
                <div class="p-2 rounded-xl bg-slate-100/70 dark:bg-white/5 border border-slate-200/80 dark:border-white/5">
                    <span class="text-slate-500 dark:text-slate-400 block font-medium">Pimpinan / Supervisor:</span>
                    <span class="font-bold text-amber-700 dark:text-amber-300" x-text="selectedAgentData.lead || 'AI Lead'"></span>
                </div>
                <div class="p-2 rounded-xl bg-slate-100/70 dark:bg-white/5 border border-slate-200/80 dark:border-white/5">
                    <span class="text-slate-500 dark:text-slate-400 block font-medium">Rekan Kerja Tim:</span>
                    <span class="font-bold text-cyan-700 dark:text-cyan-300 truncate block" x-text="(selectedAgentData.teammates && selectedAgentData.teammates.length > 0) ? selectedAgentData.teammates.join(', ') : 'Mandiri'"></span>
                </div>
            </div>

            <!-- Direct Task Giving Console -->
            <div class="space-y-2 p-3 rounded-2xl bg-gradient-to-b from-blue-50/90 to-slate-100/90 dark:from-blue-950/60 dark:to-slate-950/80 border border-cyan-500/30 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-cyan-800 dark:text-cyan-300 flex items-center gap-1.5 uppercase tracking-wide">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Beri Tugas Langsung</span>
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono" x-text="selectedAgentData.name || selectedAgent"></span>
                </div>

                <!-- Quick Task Recommendations -->
                <div class="space-y-1">
                    <div class="text-[9.5px] text-slate-500 dark:text-slate-400 font-medium">Pilihan Tugas Cepat:</div>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="chip in getQuickTasksForAgent(selectedAgent)" :key="chip">
                            <button
                                type="button"
                                @click="directTaskInput = chip"
                                class="text-[10px] px-2 py-1 rounded-lg bg-white dark:bg-white/5 hover:bg-cyan-50 dark:hover:bg-cyan-500/20 hover:text-cyan-900 dark:hover:text-cyan-200 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 transition text-left cursor-pointer"
                                x-text="chip"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- Custom Task Input -->
                <div class="relative">
                    <textarea
                        x-model="directTaskInput"
                        rows="2"
                        class="w-full text-xs bg-white dark:bg-black/50 border border-slate-300 dark:border-white/15 rounded-xl p-2 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500 transition resize-none"
                        :placeholder="`Instruksikan ${selectedAgentData.name || 'agen ini'} untuk langsung bertindak...`"
                    ></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-1.5 pt-1">
                    <button
                        type="button"
                        @click="assignDirectTask(selectedAgent, directTaskInput)"
                        :disabled="isAssigningTask"
                        class="py-2 px-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:opacity-95 text-white text-[11px] font-bold flex items-center justify-center gap-1 transition shadow-lg shadow-emerald-500/25 cursor-pointer disabled:opacity-50"
                    >
                        <i data-lucide="play" class="w-3.5 h-3.5 fill-white"></i>
                        <span>Tugaskan Agen</span>
                    </button>

                    <button
                        type="button"
                        @click="assignTeamTask(selectedAgentData.team || resolveAgentTeam(selectedAgent), directTaskInput)"
                        :disabled="isAssigningTask"
                        class="py-2 px-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:opacity-95 text-white text-[11px] font-bold flex items-center justify-center gap-1 transition shadow-lg shadow-blue-500/25 cursor-pointer disabled:opacity-50"
                    >
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        <span>Tugaskan Tim</span>
                    </button>
                </div>

                <!-- Success Feedback Notification -->
                <div x-show="taskSuccessMessage" x-transition class="p-2 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-800 dark:text-emerald-300 text-[10px] flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    <span x-text="taskSuccessMessage"></span>
                </div>
                <!-- Primary POV Control Button -->
                <button
                    type="button"
                    @click="takeAgentPOV(selectedAgent)"
                    class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-violet-600 via-indigo-600 to-cyan-600 hover:from-violet-500 hover:to-cyan-500 text-white text-xs font-bold flex items-center justify-center gap-2 transition shadow-lg shadow-indigo-500/25 cursor-pointer group"
                >
                    <span class="text-base group-hover:scale-110 transition-transform">🎮</span>
                    <span>Ambil POV Kamera &amp; Kendalikan Agen</span>
                    <span class="text-[10px] py-0.5 px-1.5 rounded-md bg-white/20 font-mono">WASD</span>
                </button>
            </div>

            <!-- Secondary Actions: Consultation Chat & Avatar -->
            <div class="flex items-center gap-2 pt-1 border-t border-slate-200 dark:border-white/10">
                <button
                    type="button"
                    @click="consultWithAgent(selectedAgentData)"
                    class="flex-1 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-800 dark:text-slate-200 text-xs font-semibold flex items-center justify-center gap-1.5 transition cursor-pointer"
                >
                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-300"></i>
                    <span>Buka Chat Konsultasi</span>
                </button>
                <button
                    type="button"
                    @click="openAvatarModal(selectedAgent)"
                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-800 dark:text-white text-xs font-medium transition flex items-center gap-1.5 cursor-pointer"
                    title="Kustomisasi Avatar Karakter Ini"
                >
                    <i data-lucide="palette" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-300"></i>
                    <span class="hidden sm:inline">Avatar</span>
                </button>
            </div>
        </div>
    </template>

    <!-- B. ROOM / FACILITY INSPECTION MODE -->
    <template x-if="selectedRoom && !selectedAgent">
        <div class="space-y-3">
            <!-- Top Header -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-white text-xl shadow-lg ring-2 ring-white/20"
                         :style="`background-color: ${selectedRoomData.color || '#1e3a8a'};`">
                        <i data-lucide="building" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <span x-text="selectedRoomData.name || selectedRoom"></span>
                        </div>
                        <div class="text-[11px] text-cyan-700 dark:text-cyan-300 font-medium" x-text="selectedRoomData.subtitle || 'Fasilitas Virtual Office'"></div>
                    </div>
                </div>
                <button @click="deselectRoom()" class="text-slate-400 hover:text-slate-900 dark:hover:text-white transition p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-white/10 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Team & Supervisor Tag -->
            <div class="flex items-center justify-between text-[11px] px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10">
                <span class="text-slate-500 dark:text-slate-400">Tim &amp; Pimpinan:</span>
                <span class="font-semibold text-amber-700 dark:text-amber-300" x-text="(selectedRoomData.teamName || 'Tim Terkait') + ' (Lead: ' + (selectedRoomData.lead || 'AI Lead') + ')'"></span>
            </div>

            <!-- Scope & Lingkup Kerja Ruangan -->
            <div class="space-y-1 p-3 rounded-2xl bg-slate-100/80 dark:bg-black/40 border border-slate-200 dark:border-white/10">
                <div class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="target" class="w-3.5 h-3.5 text-cyan-500"></i>
                    <span>Lingkup Kerja &amp; Pembagian Tugas:</span>
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

            <!-- Room Direct Team Delegation Console -->
            <div class="space-y-2 p-3 rounded-2xl bg-gradient-to-b from-blue-50/90 to-slate-100/90 dark:from-blue-950/60 dark:to-slate-950/80 border border-cyan-500/30 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-cyan-800 dark:text-cyan-300 flex items-center gap-1.5 uppercase tracking-wide">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Tugaskan Tim Ruangan</span>
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono" x-text="selectedRoomData.teamName || 'Tim Ruangan'"></span>
                </div>

                <!-- Quick Room Tasks -->
                <div class="space-y-1">
                    <div class="text-[9.5px] text-slate-500 dark:text-slate-400 font-medium">Instruksi Cepat Tim:</div>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="chip in getQuickTasksForRoom(selectedRoom)" :key="chip">
                            <button
                                type="button"
                                @click="directTaskInput = chip"
                                class="text-[10px] px-2 py-1 rounded-lg bg-white dark:bg-white/5 hover:bg-cyan-50 dark:hover:bg-cyan-500/20 hover:text-cyan-900 dark:hover:text-cyan-200 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 transition text-left cursor-pointer"
                                x-text="chip"
                            ></button>
                        </template>
                    </div>
                </div>

                <div class="relative">
                    <textarea
                        x-model="directTaskInput"
                        rows="2"
                        class="w-full text-xs bg-white dark:bg-black/50 border border-slate-300 dark:border-white/15 rounded-xl p-2 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500 transition resize-none"
                        :placeholder="`Instruksikan seluruh tim di ${selectedRoomData.name || 'ruangan ini'}...`"
                    ></textarea>
                </div>

                <button
                    type="button"
                    @click="assignTeamTask(selectedRoomData.teamKey || 'general', directTaskInput)"
                    :disabled="isAssigningTask"
                    class="w-full py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 via-blue-600 to-indigo-600 hover:opacity-95 text-white text-xs font-bold flex items-center justify-center gap-1.5 transition shadow-lg shadow-cyan-500/25 cursor-pointer disabled:opacity-50"
                >
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Kirim Tugas ke Tim Ruangan Ini</span>
                </button>

                <div x-show="taskSuccessMessage" x-transition class="p-2 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-800 dark:text-emerald-300 text-[10px] flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    <span x-text="taskSuccessMessage"></span>
                </div>
            </div>
        </div>
    </template>
</div>
