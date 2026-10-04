{{-- Interactive AI Consultation Modal (Shared across Lobby and All Offices) --}}
<div x-show="showConsultationModal" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     style="display: none;"
     @keydown.escape.window="closeConsultationModal()">

    <div class="w-full max-w-3xl rounded-3xl bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
         @click.outside="closeConsultationModal()">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-black/10 dark:border-white/10 flex items-center justify-between bg-black/[0.02] dark:bg-white/[0.02]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-black dark:bg-white text-white dark:text-black flex items-center justify-center font-bold">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-black dark:text-white">Konsultasi AI Office</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Tanyakan performa bisnis, analisa operasional, atau instruksikan draf aksi.</p>
                </div>
            </div>
            <button type="button" @click="closeConsultationModal()" class="w-8 h-8 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Body & Query Form -->
        <div class="p-6 space-y-4 overflow-y-auto flex-1">
            
            <!-- Team Assignment Selector -->
            <div class="space-y-1.5 p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                <div class="flex items-center justify-between">
                    <label class="text-[11px] font-bold text-black/70 dark:text-white/70 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-500"></i>
                        <span>Tugaskan Kepada Tim Kerja:</span>
                    </label>
                    <span class="text-[10px] text-black/40 dark:text-white/40">AI bekerja sebagai tim kolaboratif</span>
                </div>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <button type="button" 
                            @click="selectedTeam = 'auto'"
                            :class="selectedTeam === 'auto' ? 'bg-black text-white dark:bg-white dark:text-black font-bold shadow-sm' : 'bg-black/5 text-black/70 dark:bg-white/5 dark:text-white/70 hover:bg-black/10 dark:hover:bg-white/10'"
                            class="px-2.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <span>⚡ Otomatis (Rekomendasi COO)</span>
                    </button>
                    <button type="button" 
                            @click="selectedTeam = 'executive'"
                            :class="selectedTeam === 'executive' ? 'bg-amber-600 text-white font-bold shadow-sm' : 'bg-black/5 text-black/70 dark:bg-white/5 dark:text-white/70 hover:bg-black/10 dark:hover:bg-white/10'"
                            class="px-2.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <span>👑 Eksekutif (CEO / COO / CFO)</span>
                    </button>
                    <button type="button" 
                            @click="selectedTeam = 'marketing'"
                            :class="selectedTeam === 'marketing' ? 'bg-purple-600 text-white font-bold shadow-sm' : 'bg-black/5 text-black/70 dark:bg-white/5 dark:text-white/70 hover:bg-black/10 dark:hover:bg-white/10'"
                            class="px-2.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <span>🎨 Tim Marketing</span>
                    </button>
                    <button type="button" 
                            @click="selectedTeam = 'sales'"
                            :class="selectedTeam === 'sales' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-black/5 text-black/70 dark:bg-white/5 dark:text-white/70 hover:bg-black/10 dark:hover:bg-white/10'"
                            class="px-2.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <span>💼 Tim Sales</span>
                    </button>
                    <button type="button" 
                            @click="selectedTeam = 'operations'"
                            :class="selectedTeam === 'operations' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-black/5 text-black/70 dark:bg-white/5 dark:text-white/70 hover:bg-black/10 dark:hover:bg-white/10'"
                            class="px-2.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <span>📦 Tim Operasional</span>
                    </button>
                    <button type="button" 
                            @click="selectedTeam = 'finance'"
                            :class="selectedTeam === 'finance' ? 'bg-teal-600 text-white font-bold shadow-sm' : 'bg-black/5 text-black/70 dark:bg-white/5 dark:text-white/70 hover:bg-black/10 dark:hover:bg-white/10'"
                            class="px-2.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <span>💰 Tim Keuangan</span>
                    </button>
                    <button type="button" 
                            @click="selectedTeam = 'people'"
                            :class="selectedTeam === 'people' ? 'bg-rose-600 text-white font-bold shadow-sm' : 'bg-black/5 text-black/70 dark:bg-white/5 dark:text-white/70 hover:bg-black/10 dark:hover:bg-white/10'"
                            class="px-2.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <span>👥 Tim People & HR</span>
                    </button>
                </div>
            </div>

            <!-- Quick Prompts Pills -->
            <div class="space-y-1.5">
                <div class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider">Topik Cepat Berdasarkan Fungsi Tim:</div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="selectedTeam = 'executive'; quickAsk('Bagaimana kondisi kesehatan bisnis saya secara menyeluruh minggu ini?')" class="px-2.5 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-medium text-black/80 dark:text-white/80 transition text-left border border-black/5 dark:border-white/5 flex items-center gap-1">
                        <span>👑</span> <span>Analisa Kesehatan Bisnis (Eksekutif)</span>
                    </button>
                    <button type="button" @click="selectedTeam = 'operations'; quickAsk('Apakah ada stok kritis di gudang yang perlu segera dibuatkan PO reorder?')" class="px-2.5 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-medium text-black/80 dark:text-white/80 transition text-left border border-black/5 dark:border-white/5 flex items-center gap-1">
                        <span>📦</span> <span>Cek Stok Kritis & PO (Operasional)</span>
                    </button>
                    <button type="button" @click="selectedTeam = 'marketing'; quickAsk('Siapkan rekomendasi promo dan naskah konten media sosial untuk mendongkrak penjualan.')" class="px-2.5 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-medium text-black/80 dark:text-white/80 transition text-left border border-black/5 dark:border-white/5 flex items-center gap-1">
                        <span>🎨</span> <span>Rekomendasi Promo & Konten (Marketing)</span>
                    </button>
                    <button type="button" @click="selectedTeam = 'finance'; quickAsk('Cek apakah ada pelanggan yang memiliki piutang jatuh tempo.')" class="px-2.5 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-medium text-black/80 dark:text-white/80 transition text-left border border-black/5 dark:border-white/5 flex items-center gap-1">
                        <span>💰</span> <span>Piutang Jatuh Tempo (Keuangan)</span>
                    </button>
                    <button type="button" @click="selectedTeam = 'sales'; quickAsk('Evaluasi pipeline leads pelanggan dan produk terlaris minggu ini.')" class="px-2.5 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-medium text-black/80 dark:text-white/80 transition text-left border border-black/5 dark:border-white/5 flex items-center gap-1">
                        <span>💼</span> <span>Pipeline Leads & Sales (Sales)</span>
                    </button>
                </div>
            </div>

            <!-- Input Form -->
            <form @submit.prevent="submitQuery()" class="space-y-3 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Pertanyaan atau Instruksi Pemilik Usaha:</label>
                    <textarea x-model="userQuery" 
                              rows="3" 
                              placeholder="Ketik instruksi untuk tim di sini... (Contoh: Evaluasi omzet 7 hari terakhir dan buatkan draf kampanye promo)"
                              class="w-full px-4 py-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition resize-none"></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-black/40 dark:text-white/40 flex items-center gap-1">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>Tugas dialokasikan ke ruang kerja tim terkait di Virtual Office HQ.</span>
                    </span>
                    <button type="submit" 
                            :disabled="!userQuery.trim() || isSubmitting"
                            class="px-4 py-2 rounded-xl bg-black hover:bg-black/90 dark:bg-white dark:hover:bg-white/90 text-white dark:text-black text-xs font-semibold shadow-sm transition disabled:opacity-50 flex items-center gap-2">
                        <span x-text="isSubmitting ? 'Tim Sedang Memproses...' : 'Tugaskan ke Tim AI'"></span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </form>

            <!-- Processing State Banner -->
            <div x-show="isSubmitting" class="p-6 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/10 dark:border-white/10 text-center space-y-2">
                <div class="inline-block animate-spin text-black/60 dark:text-white/60">
                    <i data-lucide="loader" class="w-6 h-6"></i>
                </div>
                <div class="text-xs font-bold text-black dark:text-white">Tim AI sedang berkolaborasi di ruang kerja...</div>
                <p class="text-[11px] text-black/50 dark:text-white/50">Menganalisis basis data, mengevaluasi parameter operasional, dan merumuskan draf tindakan terkoordinasi.</p>
            </div>

            <!-- Structured Response Container -->
            <div x-show="responseResult" class="space-y-4 pt-2">
                <div class="rounded-2xl p-5 bg-black/[0.02] dark:bg-white/[0.02] border border-black/10 dark:border-white/10 space-y-4">
                    
                    <!-- Team & Room Banner -->
                    <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-xl bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-transparent border border-indigo-500/20">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                <i data-lucide="briefcase" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                                    <span x-text="responseResult?.assigned_team || 'Tim AI Terpadu'"></span>
                                    <template x-if="responseResult?.is_executive_task">
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300 uppercase">MANDAT EKSEKUTIF</span>
                                    </template>
                                </div>
                                <div class="text-[10px] text-black/50 dark:text-white/50 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-indigo-500"></i>
                                    <span x-text="responseResult?.team_room || 'Headquarters Unified Floor'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Participating Agents Squad -->
                        <div class="flex flex-wrap items-center gap-1">
                            <span class="text-[10px] text-black/40 dark:text-white/40 mr-1">Anggota Tim:</span>
                            <template x-for="agentName in (responseResult?.participating_agents || [])" :key="agentName">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80" x-text="agentName"></span>
                            </template>
                        </div>
                    </div>

                    <!-- Executive Summary -->
                    <div>
                        <div class="text-xs font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider mb-1">Executive Summary Tim</div>
                        <p class="text-xs sm:text-sm text-black dark:text-white leading-relaxed font-medium" x-text="responseResult?.summary"></p>
                    </div>

                    <!-- Findings -->
                    <template x-if="responseResult?.findings && responseResult.findings.length > 0">
                        <div>
                            <div class="text-xs font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider mb-2">Temuan Faktual</div>
                            <ul class="space-y-1 text-xs text-black/70 dark:text-white/70">
                                <template x-for="(finding, i) in responseResult.findings" :key="i">
                                    <li class="flex items-start gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                        <span x-text="finding"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <!-- Recommendations -->
                    <template x-if="responseResult?.recommendations && responseResult.recommendations.length > 0">
                        <div>
                            <div class="text-xs font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider mb-2">Rekomendasi Strategis</div>
                            <ul class="space-y-1 text-xs text-black/70 dark:text-white/70">
                                <template x-for="(rec, i) in responseResult.recommendations" :key="i">
                                    <li class="flex items-start gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                                        <span x-text="rec"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <!-- Proposed Actions -->
                    <template x-if="responseResult?.proposals && responseResult.proposals.length > 0">
                        <div class="pt-3 border-t border-black/5 dark:border-white/5 space-y-3">
                            <div class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Usulan Aksi Bisnis Disiapkan (Menunggu Persetujuan)</div>
                            
                            <div class="grid grid-cols-1 gap-2.5">
                                <template x-for="prop in responseResult.proposals" :key="prop.id">
                                    <div class="p-3.5 rounded-xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-black dark:text-white" x-text="prop.title"></span>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300 uppercase" x-text="prop.risk_level"></span>
                                            </div>
                                            <div class="text-xs text-black/60 dark:text-white/60" x-text="prop.description"></div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <a href="{{ route('cooca-ai.actions') }}" class="px-3 py-1.5 rounded-lg bg-black dark:bg-white text-white dark:text-black text-xs font-semibold">
                                                Buka Action Center &rarr;
                                            </a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                </div>
            </div>

                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-black/10 dark:border-white/10 flex items-center justify-between text-xs text-black/50 dark:text-white/50 bg-black/[0.01] dark:bg-white/[0.01]">
            <span>AI bekerja untuk bisnis. Manusia tetap pemegang keputusan akhir.</span>
            <button type="button" @click="closeConsultationModal()" class="px-3.5 py-1.5 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 font-semibold text-black dark:text-white transition">Tutup</button>
        </div>

    </div>
</div>
