<!-- ========================================================================================= -->
<!-- COOCA SMART AI ASSISTANT & SYSTEM GUIDE MODAL SHEET (Bento Apple HIG Full-Size XXL)       -->
<!-- ========================================================================================= -->
<div x-data="aiAssistantModal()" 
     x-init="initAssistant()" 
     @open-ai-assistant.window="open($event.detail?.scope)" 
     x-cloak>

    <!-- Floating Trigger Bubble (Bottom Right) -->
    <div class="fixed bottom-6 right-6 z-40 print:hidden">
        <button type="button" 
                @click="open()" 
                title="Buka AI Assistant & Panduan SOP (Ctrl + K)" 
                class="group relative flex items-center gap-2.5 px-4 py-3 rounded-full bg-gradient-to-r from-[#007AFF] to-[#5856D6] text-white shadow-xl shadow-[#007AFF]/25 hover:shadow-2xl hover:shadow-[#007AFF]/35 hover:scale-[1.03] active:scale-[0.97] transition-all duration-200">
            <div class="w-5 h-5 flex items-center justify-center">
                <i data-lucide="sparkles" class="w-5 h-5 transition-transform group-hover:rotate-12"></i>
            </div>
            <span class="text-[13px] font-semibold tracking-tight hidden sm:inline">Tanya Asisten</span>
            <kbd class="hidden md:inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-white/20 text-[10px] font-medium tracking-widest text-white/90">
                ⌘K
            </kbd>
            <span class="absolute -top-1 -right-1 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 border-2 border-white dark:border-[#1C1C1E]"></span>
            </span>
        </button>
    </div>

    <!-- Backdrop & Modal Container -->
    <div x-show="isOpen" 
         class="fixed inset-0 z-50 overflow-hidden bg-black/50 backdrop-blur-md flex items-end sm:items-center justify-center sm:p-4 lg:p-6"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Modal Canvas (Full-Size XXL Bento Apple HIG) -->
        <div class="w-full sm:max-w-4xl lg:max-w-5xl h-[92vh] sm:h-[86vh] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-t-[28px] sm:rounded-[24px] shadow-2xl border border-black/10 dark:border-white/10 flex flex-col overflow-hidden"
             @click.outside="close()"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95">
            
            <!-- Top Toolbar / Header -->
            <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between gap-4 bg-black/[0.01] dark:bg-white/[0.01] shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-[11px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center shadow-sm shrink-0">
                        <i data-lucide="bot" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">COOCA Assistant</h3>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-medium tracking-wide flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                SOP &amp; System Ready
                            </span>
                        </div>
                        <p class="text-[11px] text-black/45 dark:text-white/45">Panduan SOP Internal Usaha &amp; Penggunaan Fitur 20 Sektor COOCA</p>
                    </div>
                </div>

                <!-- Scope Switcher (Segmented Control) -->
                <div class="hidden md:inline-flex p-1 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-[12px] font-medium">
                    <button type="button" @click="scope = 'all'" :class="scope === 'all' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-xs' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white'" class="px-3 py-1 rounded-[7px] transition-all">
                        Semua
                    </button>
                    <button type="button" @click="scope = 'sop'" :class="scope === 'sop' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-xs' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white'" class="px-3 py-1 rounded-[7px] transition-all flex items-center gap-1.5">
                        <i data-lucide="file-check" class="w-3.5 h-3.5 text-indigo-500"></i>
                        <span>SOP Usaha</span>
                    </button>
                    <button type="button" @click="scope = 'system'" :class="scope === 'system' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-xs' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white'" class="px-3 py-1 rounded-[7px] transition-all flex items-center gap-1.5">
                        <i data-lucide="settings" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Fitur COOCA</span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="clearHistory()" title="Bersihkan Obrolan" class="p-2 rounded-[8px] text-black/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </button>
                    <button type="button" @click="close()" class="p-2 rounded-[8px] text-black/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Scope Switcher Bar -->
            <div class="flex md:hidden px-4 py-2 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] justify-center">
                <div class="inline-flex p-0.5 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] text-[11px] font-medium w-full max-w-sm">
                    <button type="button" @click="scope = 'all'" :class="scope === 'all' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-xs' : 'text-black/50 dark:text-white/50'" class="flex-1 py-1 rounded-[6px] text-center">Semua</button>
                    <button type="button" @click="scope = 'sop'" :class="scope === 'sop' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-xs' : 'text-black/50 dark:text-white/50'" class="flex-1 py-1 rounded-[6px] text-center">SOP Usaha</button>
                    <button type="button" @click="scope = 'system'" :class="scope === 'system' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-xs' : 'text-black/50 dark:text-white/50'" class="flex-1 py-1 rounded-[6px] text-center">Fitur COOCA</button>
                </div>
            </div>

            <!-- Chat Thread (Scrollable Area) -->
            <div id="ai-assistant-messages" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6">
                <!-- Welcome Screen if No Messages -->
                <template x-if="messages.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-center max-w-xl mx-auto py-8">
                        <div class="w-14 h-14 rounded-[18px] bg-gradient-to-tr from-[#007AFF]/15 to-[#5856D6]/15 border border-[#007AFF]/20 text-[#007AFF] flex items-center justify-center mb-4 shadow-sm">
                            <i data-lucide="sparkles" class="w-7 h-7"></i>
                        </div>
                        <h4 class="text-[18px] font-bold text-black dark:text-white tracking-tight">Ada yang bisa dibantu hari ini?</h4>
                        <p class="text-[13px] text-black/50 dark:text-white/50 leading-relaxed mt-1.5 mb-6">
                            Tanyakan cara menggunakan fitur COOCA (POS, Kasir, Stok, WhatsApp, Jurnal) atau tanyakan aturan SOP internal yang sudah diunggah oleh Business Owner.
                        </p>

                        <!-- Quick Prompts Pills -->
                        <div class="w-full space-y-2 text-left">
                            <p class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider px-1">Pertanyaan Cepat Rekomendasi</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <template x-for="p in prompts" :key="p.text">
                                    <button type="button" 
                                            @click="askQuickPrompt(p.text, p.scope)"
                                            class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/15 border border-black/5 dark:border-white/5 hover:border-[#007AFF]/30 text-left transition-all text-[12px] text-black/80 dark:text-white/80 font-medium flex items-center gap-2.5 group">
                                        <i data-lucide="message-square" class="w-4 h-4 text-[#007AFF] shrink-0 group-hover:scale-110 transition-transform"></i>
                                        <span class="line-clamp-2" x-text="p.text"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Messages Thread -->
                <template x-for="(msg, index) in messages" :key="index">
                    <div class="flex flex-col space-y-2">
                        <!-- USER MESSAGE -->
                        <template x-if="msg.role === 'user'">
                            <div class="flex items-start justify-end gap-2.5">
                                <div class="max-w-[85%] sm:max-w-xl rounded-[18px] rounded-br-[4px] bg-[#007AFF] text-white px-4 py-3 text-[13px] shadow-sm leading-relaxed whitespace-pre-wrap">
                                    <span x-text="msg.content"></span>
                                </div>
                                <div class="w-7 h-7 rounded-full bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60 flex items-center justify-center shrink-0 mt-0.5 text-[11px] font-bold">
                                    U
                                </div>
                            </div>
                        </template>

                        <!-- ASSISTANT MESSAGE -->
                        <template x-if="msg.role === 'assistant'">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-[10px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                    <i data-lucide="bot" class="w-4 h-4"></i>
                                </div>
                                
                                <div class="max-w-[92%] sm:max-w-2xl rounded-[18px] rounded-tl-[4px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 p-4 sm:p-5 text-[13px] text-black/90 dark:text-white/90 shadow-xs space-y-4">
                                    <!-- Rendered Content (Markdown) -->
                                    <div class="prose prose-sm dark:prose-invert max-w-none leading-relaxed text-[13px]" x-html="renderMarkdown(msg.content)"></div>

                                    <!-- Action Buttons (Deep Links to Setting/Feature) -->
                                    <template x-if="msg.action_buttons && msg.action_buttons.length > 0">
                                        <div class="pt-3 border-t border-black/5 dark:border-white/5 flex flex-wrap gap-2">
                                            <template x-for="btn in msg.action_buttons" :key="btn.url">
                                                <a :href="btn.url" 
                                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[8px] bg-[#007AFF] hover:bg-[#0071EB] text-white text-[12px] font-medium shadow-xs transition-transform active:scale-[0.98]">
                                                    <i data-lucide="arrow-right-circle" class="w-3.5 h-3.5"></i>
                                                    <span x-text="btn.label"></span>
                                                </a>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Sources Reference Badge -->
                                    <template x-if="msg.sources && msg.sources.length > 0">
                                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex flex-wrap items-center gap-1.5 text-[11px] text-black/50 dark:text-white/50">
                                            <span class="font-medium">Rujukan:</span>
                                            <template x-for="src in msg.sources" :key="src.title + src.subtitle">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">
                                                    <i :data-lucide="src.type === 'sop' ? 'file-check' : 'book-open'" class="w-3 h-3 text-[#007AFF]"></i>
                                                    <span x-text="src.title"></span>
                                                    <span class="opacity-60" x-text="'(' + src.subtitle + ')'"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Loading Bubble -->
                <div x-show="loading" class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-[10px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center shrink-0 mt-0.5 animate-pulse">
                        <i data-lucide="bot" class="w-4 h-4"></i>
                    </div>
                    <div class="rounded-[18px] rounded-tl-[4px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 px-4 py-3 text-[13px] text-black/50 dark:text-white/50 flex items-center gap-2 shadow-xs">
                        <div class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-bounce" style="animation-delay: 0ms"></span>
                            <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-bounce" style="animation-delay: 150ms"></span>
                            <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-bounce" style="animation-delay: 300ms"></span>
                        </div>
                        <span>Menganalisis SOP usaha &amp; modul sistem...</span>
                    </div>
                </div>
            </div>

            <!-- Input Bar (Apple HIG Floating Text Input) -->
            <div class="p-4 sm:p-5 border-t border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E] shrink-0">
                <form @submit.prevent="submitQuestion()" class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input type="text" 
                               x-model="query" 
                               x-ref="queryInput"
                               :disabled="loading"
                               placeholder="Ketik pertanyaan (Contoh: cara rekap kasir, aturan buka toko, setting printer)..." 
                               class="w-full pl-4 pr-10 py-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 disabled:opacity-50 transition-all">
                        
                        <button type="button" 
                                x-show="query.length > 0" 
                                @click="query = ''" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-black/30 hover:text-black dark:text-white/30 dark:hover:text-white">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <button type="submit" 
                            :disabled="loading || query.trim().length === 0" 
                            class="px-5 py-3 rounded-[14px] bg-[#007AFF] hover:bg-[#0071EB] disabled:opacity-40 text-white text-[13px] font-medium shadow-md shadow-[#007AFF]/20 transition-all flex items-center justify-center gap-1.5 shrink-0 active:scale-[0.97]">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Kirim</span>
                    </button>
                </form>
                <div class="flex items-center justify-between text-[11px] text-black/40 dark:text-white/40 mt-2 px-1">
                    <span>Tekan <kbd class="px-1 py-0.5 rounded bg-black/5 dark:bg-white/10">Enter</kbd> untuk mengirim pertanyaan</span>
                    <a href="{{ route('settings.sop.index') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1">
                        <i data-lucide="upload" class="w-3 h-3"></i>
                        <span>Kelola PDF SOP Usaha</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('aiAssistantModal', () => ({
        isOpen: false,
        scope: 'all',
        query: '',
        loading: false,
        conversationId: null,
        messages: [],
        prompts: [],

        initAssistant() {
            // Global keybinding Ctrl+K / Cmd+K
            window.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    if (this.isOpen) {
                        this.close();
                    } else {
                        this.open();
                    }
                }
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });

            this.loadPrompts();
        },

        open(targetScope = null) {
            this.isOpen = true;
            if (targetScope) {
                this.scope = targetScope;
            }
            this.$nextTick(() => {
                this.refreshIcons();
                if (this.$refs.queryInput) {
                    this.$refs.queryInput.focus();
                }
                this.scrollToBottom();
            });
            if (this.messages.length === 0) {
                this.loadHistory();
            }
        },

        close() {
            this.isOpen = false;
        },

        async loadPrompts() {
            try {
                const res = await fetch('/assistant/prompts', {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.success && json.data) {
                    this.prompts = json.data;
                    this.$nextTick(() => this.refreshIcons());
                }
            } catch (e) {
                console.error('Failed to load prompts', e);
            }
        },

        async loadHistory() {
            try {
                const res = await fetch('/assistant/history', {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.success && json.data) {
                    this.conversationId = json.data.conversation_id;
                    if (json.data.messages && json.data.messages.length > 0) {
                        this.messages = json.data.messages;
                        this.$nextTick(() => {
                            this.refreshIcons();
                            this.scrollToBottom();
                        });
                    }
                }
            } catch (e) {
                console.error('Failed to load history', e);
            }
        },

        askQuickPrompt(text, promptScope) {
            this.query = text;
            if (promptScope) {
                this.scope = promptScope;
            }
            this.submitQuestion();
        },

        async submitQuestion() {
            const q = this.query.trim();
            if (!q || this.loading) return;

            // Push User Message
            this.messages.push({
                role: 'user',
                content: q,
                created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });

            this.query = '';
            this.loading = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/assistant/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        query: q,
                        scope: this.scope,
                        conversation_id: this.conversationId
                    })
                });

                const json = await res.json();
                if (json.success && json.data) {
                    this.conversationId = json.data.conversation_id;
                    this.messages.push({
                        role: 'assistant',
                        content: json.data.content,
                        sources: json.data.sources || [],
                        action_buttons: json.data.action_buttons || [],
                        created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    });
                } else {
                    this.messages.push({
                        role: 'assistant',
                        content: 'Maaf, terjadi kendala saat memproses jawaban: ' + (json.message || 'Silakan coba lagi.'),
                        sources: [],
                        action_buttons: []
                    });
                }
            } catch (err) {
                this.messages.push({
                    role: 'assistant',
                    content: 'Koneksi terputus. Pastikan server aktif dan coba kembali.',
                    sources: [],
                    action_buttons: []
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    this.refreshIcons();
                    this.scrollToBottom();
                });
            }
        },

        async clearHistory() {
            if (!confirm('Bersihkan seluruh percakapan dengan asisten?')) return;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                await fetch('/assistant/clear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                this.messages = [];
                this.conversationId = null;
            } catch (e) {
                console.error(e);
            }
        },

        scrollToBottom() {
            const el = document.getElementById('ai-assistant-messages');
            if (el) {
                el.scrollTop = el.scrollHeight;
            }
        },

        refreshIcons() {
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        },

        renderMarkdown(raw) {
            if (!raw) return '';
            let html = raw
                // Headings
                .replace(/^### (.*$)/gim, '<h4 class="text-[14px] font-bold text-black dark:text-white mt-3 mb-1.5">$1</h4>')
                .replace(/^## (.*$)/gim, '<h3 class="text-[15px] font-bold text-black dark:text-white mt-3.5 mb-2">$1</h3>')
                .replace(/^# (.*$)/gim, '<h2 class="text-[16px] font-bold text-black dark:text-white mt-4 mb-2">$1</h2>')
                // Bold & Italic
                .replace(/\*\*(.*?)\*\*/gim, '<strong class="font-semibold text-black dark:text-white">$1</strong>')
                .replace(/\*(.*?)\*/gim, '<em class="italic opacity-90">$1</em>')
                // Horizontal Rule
                .replace(/^---$/gim, '<hr class="my-3 border-black/10 dark:border-white/10">')
                // Line breaks
                .replace(/\n\n/gim, '<br><br>')
                .replace(/\n/gim, '<br>');
            return html;
        }
    }));
});
</script>
