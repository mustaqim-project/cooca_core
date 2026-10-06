{{-- Interactive AI Chat Modal (Pusat Chat AI Virtual Office — Identik Gambar 1) --}}
<div x-show="showConsultationModal" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 z-[150] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm"
     style="display: none;"
     @keydown.escape.window="closeConsultationModal()">

    <div class="w-full max-w-xl sm:max-w-2xl rounded-3xl bg-[#0B0F19] border border-white/10 text-white shadow-2xl overflow-hidden flex flex-col h-[650px] max-h-[92vh]"
         @click.outside="closeConsultationModal()">
        
        <!-- Header (Identik Gambar 1) -->
        <div class="px-5 py-3.5 border-b border-white/10 flex items-center justify-between bg-[#0B0F19] shrink-0">
            <div class="flex items-center gap-3">
                <!-- Circular Bot Avatar -->
                <div class="w-10 h-10 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-400 flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="bot" class="w-5 h-5"></i>
                </div>
                <!-- Title & Agent Selector Dropdown -->
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-white tracking-tight">{{ __('ai.chat.title') }}</h3>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block shadow-sm shadow-emerald-400/50"></span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-white/70">
                        <span>{{ __('ai.chat.select_agent') }}</span>
                        <select x-model="activeChatAgent" 
                                @change="switchAgent(activeChatAgent)" 
                                class="bg-[#111827] border border-white/15 text-white font-semibold text-xs rounded-lg px-2 py-0.5 focus:outline-none focus:border-emerald-500 transition cursor-pointer">
                            <optgroup label="Eksekutif C-Level" class="bg-[#0B0F19] text-white/60 font-semibold">
                                <option value="ceo" class="text-white">AI CEO (Direktur Utama)</option>
                                <option value="cfo" class="text-white">AI CFO (Direktur Keuangan)</option>
                                <option value="coo" class="text-white">AI COO (Direktur Operasional)</option>
                                <option value="cmo" class="text-white">AI CMO (Direktur Pemasaran)</option>
                                <option value="sales_director" class="text-white">AI Sales Director (Direktur Penjualan)</option>
                                <option value="hr_lead" class="text-white">AI HR Lead (Pimpinan SDM)</option>
                            </optgroup>
                            <optgroup label="Spesialis Operasional & Penjualan" class="bg-[#0B0F19] text-white/60 font-semibold">
                                <option value="business" class="text-white">AI Business Analyst (Strategi & Risiko)</option>
                                <option value="sales" class="text-white">AI Sales Specialist (Kasir & Transaksi)</option>
                                <option value="customer" class="text-white">AI Customer Care (Retensi Pelanggan)</option>
                                <option value="inventory" class="text-white">AI Inventory Specialist (Stok & Gudang)</option>
                                <option value="purchasing" class="text-white">AI Purchasing Specialist (Pengadaan)</option>
                                <option value="marketplace" class="text-white">AI Marketplace Specialist (Sinkronisasi)</option>
                            </optgroup>
                            <optgroup label="Spesialis Finansial, Konten & SDM" class="bg-[#0B0F19] text-white/60 font-semibold">
                                <option value="finance" class="text-white">AI Finance Specialist (Arus Kas & Audit)</option>
                                <option value="reporting" class="text-white">AI Reporting Specialist (Laporan Bisnis)</option>
                                <option value="marketing" class="text-white">AI Marketing Specialist (Kampanye Iklan)</option>
                                <option value="content" class="text-white">AI Content Specialist (Katalog & Promo)</option>
                                <option value="social_media" class="text-white">AI Social Media Specialist (Audiens)</option>
                                <option value="hr" class="text-white">AI HR Specialist (Presensi Karyawan)</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex items-center gap-1.5">
                <button type="button" 
                        @click="clearCurrentAgentChat()" 
                        title="{{ __('ai.chat.new_chat') }}" 
                        class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white/50 hover:text-white transition cursor-pointer">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" 
                        @click="closeConsultationModal()" 
                        title="{{ __('ai.chat.close') }}"
                        class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white/60 hover:text-white transition cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Chat Stream Body (Balon Percakapan Identik Gambar 1) -->
        <div id="cooca-chat-message-stream" class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 bg-[#070B12]/80 scroll-smooth">
            
            <!-- Alert jika belum ada provider AI aktif -->
            <template x-if="!hasConfiguredProvider">
                <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-200 text-xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="key" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>{{ __('ai.chat.unconfigured_warning') }}</span>
                    </div>
                    <a href="{{ route('cooca-ai.providers') }}" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-black font-bold text-xs shrink-0 transition flex items-center gap-1">
                        <span>{{ __('ai.chat.setup_api_key') }}</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </template>

            <!-- Message Stream Loop -->
            <template x-for="(msg, index) in currentChatMessages" :key="index">
                <div>
                    <!-- AI Message (Left) -->
                    <template x-if="msg.sender === 'ai'">
                        <div class="flex flex-col items-start max-w-[85%] space-y-1">
                            <span class="text-[11px] text-white/40 pl-1" x-text="msg.time"></span>
                            <div class="bg-emerald-600 text-white rounded-2xl rounded-tl-sm px-4 py-3 text-xs leading-relaxed shadow-sm">
                                <div class="whitespace-pre-line" x-text="msg.text"></div>
                                <template x-if="msg.needs_provider">
                                    <div class="mt-2.5 pt-2 border-t border-white/20">
                                        <a href="{{ route('cooca-ai.providers') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white text-emerald-900 font-bold text-xs hover:bg-emerald-50 transition">
                                            <i data-lucide="key" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('ai.chat.open_settings') }}</span>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- User Message (Right) -->
                    <template x-if="msg.sender === 'user'">
                        <div class="flex flex-col items-end max-w-[85%] ml-auto space-y-1">
                            <span class="text-[11px] text-white/40 pr-1" x-text="msg.time"></span>
                            <div class="bg-emerald-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-xs leading-relaxed shadow-sm font-medium">
                                <div class="whitespace-pre-line" x-text="msg.text"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Typing Loading Indicator -->
            <div x-show="isSendingChatMessage" class="flex flex-col items-start max-w-[85%] space-y-1" style="display: none;">
                <span class="text-[11px] text-white/40 pl-1">Mengetik...</span>
                <div class="bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 rounded-2xl rounded-tl-sm px-4 py-3 text-xs flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse delay-150"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse delay-300"></span>
                    <span class="ml-1 text-[11px]">{{ __('ai.chat.typing') }}</span>
                </div>
            </div>

        </div>

        <!-- Quick Prompt Recommendations Chips (Identik Gambar 1) -->
        <div class="px-4 py-2.5 border-t border-white/10 bg-[#090D16] flex items-center gap-2 overflow-x-auto no-scrollbar shrink-0">
            <template x-for="(p, idx) in activeQuickPrompts" :key="idx">
                <button type="button" 
                        @click="clickQuickPrompt(p)" 
                        :disabled="isSendingChatMessage"
                        class="px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white/80 hover:text-white text-xs font-medium shrink-0 transition flex items-center gap-1.5 cursor-pointer active:scale-95 disabled:opacity-50">
                    <i :data-lucide="p.icon" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span x-text="p.label"></span>
                </button>
            </template>
        </div>

        <!-- Input Bar (Identik Gambar 1) -->
        <div class="p-3 sm:p-4 bg-[#0B0F19] border-t border-white/10 flex items-center gap-2 shrink-0">
            <div class="relative flex-1">
                <input type="text" 
                       x-model="chatInput" 
                       @keydown.enter.prevent="sendChatMessage()" 
                       placeholder="{{ __('ai.chat.input_placeholder') }}" 
                       class="w-full bg-[#111827] border border-emerald-500/30 focus:border-emerald-400 rounded-xl px-4 py-2.5 text-xs text-white placeholder:text-white/40 focus:outline-none transition">
            </div>
            <button type="button" 
                    @click="sendChatMessage()" 
                    :disabled="isSendingChatMessage || !chatInput.trim()" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:hover:bg-emerald-600 text-white text-xs font-bold transition flex items-center gap-1.5 shrink-0 shadow-sm cursor-pointer active:scale-95">
                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                <span>{{ __('ai.chat.send') }}</span>
            </button>
        </div>

    </div>
</div>
