@extends('layouts.app')

@section('title', __('mcp.page_title'))

@section('content')
<script>
function mcpSettingsHub() {
    const appBasePath = {{ json_encode(base_path()) }};
    const sseUrl = {{ json_encode($sseEndpoint) }};
    const apiBase = {{ json_encode($apiBaseUrl) }};

    return {
        openCreateModal: false,
        activeProviderTab: 'claude',
        customToken: @json(session('plain_token') ?? ''),
        activeOs: 'windows',
        cursorMode: 'sse',
        toastVisible: false,
        toastMessage: '',
        toastTimeout: null,

        init() {
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            });
            this.$watch('activeProviderTab', () => {
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined' && lucide.createIcons) {
                        lucide.createIcons();
                    }
                });
            });
        },

        getToken() {
            return (this.customToken && this.customToken.trim().length > 0)
                ? this.customToken.trim()
                : 'cooca_mcp_YOUR_TOKEN';
        },

        showToast(message) {
            this.toastMessage = message || "{{ __('mcp.copied') }}";
            this.toastVisible = true;
            if (this.toastTimeout) clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => {
                this.toastVisible = false;
            }, 2500);
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            });
        },

        copyText(text, message) {
            if (!text) return;
            const self = this;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    self.showToast(message);
                }).catch(() => {
                    self.fallbackCopy(text, message);
                });
            } else {
                self.fallbackCopy(text, message);
            }
        },

        fallbackCopy(text, message) {
            const el = document.createElement('textarea');
            el.value = text;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            try {
                document.execCommand('copy');
                this.showToast(message);
            } catch (err) {
                console.error('Fallback copy failed', err);
            }
            document.body.removeChild(el);
        },

        getClaudePath() {
            if (this.activeOs === 'windows') {
                return '%APPDATA%\\Claude\\claude_desktop_config.json';
            }
            if (this.activeOs === 'macos') {
                return '~/Library/Application Support/Claude/claude_desktop_config.json';
            }
            return '~/.config/Claude/claude_desktop_config.json';
        },

        getClaudeConfig() {
            const config = {
                "mcpServers": {
                    "cooca-erp": {
                        "command": "php",
                        "args": ["artisan", "mcp:serve", "--token=" + this.getToken()],
                        "cwd": appBasePath
                    }
                }
            };
            return JSON.stringify(config, null, 2);
        },

        getClaudeCodeCmd() {
            return `claude mcp add cooca-erp -- php artisan mcp:serve --token=${this.getToken()}`;
        },

        getCursorSseConfig() {
            const config = {
                "mcpServers": {
                    "cooca-erp": {
                        "url": sseUrl,
                        "headers": {
                            "Authorization": "Bearer " + this.getToken()
                        }
                    }
                }
            };
            return JSON.stringify(config, null, 2);
        },

        getCursorStdioConfig() {
            const config = {
                "mcpServers": {
                    "cooca-erp": {
                        "command": "php",
                        "args": ["artisan", "mcp:serve", "--token=" + this.getToken()],
                        "cwd": appBasePath
                    }
                }
            };
            return JSON.stringify(config, null, 2);
        },

        getGeminiPythonCode() {
            const token = this.getToken();
            return `import requests

# Konfigurasi Akses COOCA MCP Server via REST Bridge
COOCA_TOKEN = "${token}"
COOCA_API_BASE = "${apiBase}"

# 1. Panggil Tool: Laporan Ringkasan Laba Rugi Bisnis
response = requests.post(
    f"{COOCA_API_BASE}/tools/report_get_profit_loss/execute",
    headers={"Authorization": f"Bearer {COOCA_TOKEN}"},
    json={"period": "this_month"}
)
print("Ringkasan Laba Rugi Toko:", response.json())

# 2. Panggil Tool: Cek Saldo Kas & Rekening Bank Toko
bank_resp = requests.post(
    f"{COOCA_API_BASE}/tools/finance_get_cash_and_bank_balances/execute",
    headers={"Authorization": f"Bearer {COOCA_TOKEN}"},
    json={}
)
print("Likuiditas Kas & Bank Toko:", bank_resp.json())`;
        },

        getGeminiCurl() {
            const token = this.getToken();
            return `curl -X POST "${apiBase}/tools/finance_get_cash_and_bank_balances/execute" \\
  -H "Authorization: Bearer ${token}" \\
  -H "Content-Type: application/json"`;
        },

        getOllamaInspectorCmd() {
            const token = this.getToken();
            return `npx @modelcontextprotocol/inspector php artisan mcp:serve --token=${token}`;
        },

        getLangChainCode() {
            const token = this.getToken();
            return `from langchain.tools import tool
import requests

COOCA_TOKEN = "${token}"
API_BASE = "${apiBase}"

@tool
def check_inventory_stock(query: str = "") -> str:
    """Memeriksa ketersediaan stok produk COOCA ERP."""
    resp = requests.post(
        f"{API_BASE}/tools/inventory_check_stock/execute",
        headers={"Authorization": f"Bearer {COOCA_TOKEN}"},
        json={"search_query": query}
    )
    return str(resp.json())`;
        }
    };
}

// 1. Assign to window so Alpine evaluates x-data="mcpSettingsHub()" directly
window.mcpSettingsHub = mcpSettingsHub;

// 2. Register with Alpine.data if Alpine is already loaded or on alpine:init
if (typeof Alpine !== 'undefined' && Alpine.data) {
    Alpine.data('mcpSettingsHub', mcpSettingsHub);
}
document.addEventListener('alpine:init', () => {
    if (typeof Alpine !== 'undefined' && Alpine.data) {
        Alpine.data('mcpSettingsHub', mcpSettingsHub);
    }
});
</script>

<div x-data="mcpSettingsHub()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-32 space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400 mb-1">
                <a href="{{ route('settings.index') }}" class="hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors">Pengaturan</a>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <span class="text-neutral-900 dark:text-neutral-100 font-medium">Integrasi AI (MCP)</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-neutral-900 dark:text-white">
                {{ __('mcp.page_title') }}
            </h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1 max-w-3xl">
                {{ __('mcp.page_subtitle') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="openCreateModal = true"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] rounded-[14px] bg-neutral-900 hover:bg-black text-white dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-100 font-medium text-sm shadow-sm transition-all active:scale-[0.98]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('mcp.btn_create_token') }}</span>
            </button>
        </div>
    </div>

    {{-- Alert: Token Baru Saja Dibuat (One-Time View) --}}
    @if(session('plain_token'))
    <div class="rounded-[20px] bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-500/20 p-5 backdrop-blur-md">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="key" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">
                    {{ __('mcp.token_created_success') }} ({{ session('token_name') }})
                </h4>
                <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                    {{ __('mcp.alert_new_token') }}
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        value="{{ session('plain_token') }}"
                        id="newGeneratedToken"
                        class="w-full max-w-xl font-mono text-xs bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 px-3 py-2 rounded-[10px] border border-emerald-500/30 focus:outline-none select-all" />
                    <button
                        type="button"
                        @click="copyText('{{ session('plain_token') }}')"
                        class="px-3 py-2 rounded-[10px] bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span>{{ __('mcp.btn_copy_token') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- BENTO GRID UTAMA (4 KARTU) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- KARTU 1: Master Status & Metrics Cockpit (4-Kolom) --}}
        <div class="lg:col-span-4 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800/80">
                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                        Status Master
                    </span>
                    @if($isMcpActive)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ __('mcp.status_active') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-neutral-500/10 text-neutral-600 dark:text-neutral-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-400"></span>
                            {{ __('mcp.status_inactive') }}
                        </span>
                    @endif
                </div>

                <div class="mt-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('mcp.active_tokens') }}</div>
                            <div class="text-2xl font-semibold text-neutral-900 dark:text-white tabular-nums tracking-tight mt-0.5">
                                {{ $activeTokensCount }}
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-[14px] bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-600 dark:text-neutral-300">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-neutral-100 dark:border-neutral-800/60">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('mcp.monthly_calls') }}</div>
                            <div class="text-2xl font-semibold text-neutral-900 dark:text-white tabular-nums tracking-tight mt-0.5">
                                {{ number_format($totalCallsThisMonth, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-[14px] bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-600 dark:text-neutral-300">
                            <i data-lucide="activity" class="w-5 h-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    Saklar Induk MCP
                </span>
                <form action="{{ route('settings.integrations.mcp.toggle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="is_active" value="{{ $isMcpActive ? '0' : '1' }}">
                    <button
                        type="submit"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $isMcpActive ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-neutral-700' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $isMcpActive ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- KARTU 2: Kunci Akses & Token Registry (8-Kolom) --}}
        <div class="lg:col-span-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                        {{ __('mcp.tokens_title') }}
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ __('mcp.tokens_desc') }}
                    </p>
                </div>
                <span class="text-xs text-neutral-500 dark:text-neutral-400 tabular-nums">
                    {{ count($tokens) }} Kunci Terdaftar
                </span>
            </div>

            <div class="mt-4 divide-y divide-neutral-100 dark:divide-neutral-800/80">
                @forelse($tokens as $token)
                <div class="py-3.5 flex items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                                {{ $token->name }}
                            </span>
                            <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                                {{ strtoupper($token->provider_hint) }}
                            </span>
                            @if(! $token->is_active)
                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] bg-rose-500/10 text-rose-500">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            <span class="font-mono text-[11px] text-neutral-400 dark:text-neutral-500">
                                cooca_mcp_live_••••••{{ substr($token->token_hash, -6) }}
                            </span>
                            <span>•</span>
                            <span>{{ count($token->abilities ?? []) }} Izin</span>
                            <span>•</span>
                            <span>Digunakan: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Belum pernah' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <form action="{{ route('settings.integrations.mcp.tokens.revoke', $token) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mencabut kunci akses ini?')">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="p-2 rounded-[10px] text-neutral-400 hover:text-rose-600 hover:bg-rose-500/10 transition-colors"
                                title="Cabut Token">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                    {{ __('mcp.empty_tokens') }}
                </div>
                @endforelse
            </div>
        </div>

        {{-- KARTU 3: Universal Multi-Provider Setup & Step-by-Step Tutorial Hub (12-Kolom) --}}
        <div class="lg:col-span-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 sm:p-7 shadow-sm space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-5 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-[8px] bg-blue-500/10 text-blue-600 dark:text-blue-400 font-mono text-[11px] font-semibold uppercase tracking-wider">
                            Interactive Tutorial Hub
                        </span>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            {{ __('mcp.setup_hub_title') }}
                        </h3>
                    </div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 max-w-2xl">
                        {{ __('mcp.setup_hub_desc') }}
                    </p>
                </div>

                {{-- Live Token Auto-Fill Selector & OS Switcher --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 bg-neutral-50 dark:bg-neutral-900/90 p-3 rounded-[18px] border border-neutral-200/80 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 whitespace-nowrap">
                            Token Aktif:
                        </span>
                        <input
                            type="text"
                            x-model="customToken"
                            placeholder="cooca_mcp_live_••••••"
                            class="text-xs font-mono px-3 py-1.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-neutral-200 dark:border-neutral-700 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-1 focus:ring-blue-500 w-48 sm:w-56" />
                        @if(session('plain_token'))
                            <button
                                type="button"
                                @click="customToken = '{{ session('plain_token') }}'"
                                class="px-2 py-1.5 rounded-[8px] bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[11px] font-medium whitespace-nowrap transition-colors"
                                title="Gunakan token baru">
                                Pakai Token Baru
                            </button>
                        @endif
                    </div>

                    <div class="hidden sm:block h-4 w-px bg-neutral-200 dark:bg-neutral-800"></div>

                    <div class="flex items-center gap-1">
                        <span class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 mr-1">OS:</span>
                        <button
                            type="button"
                            @click="activeOs = 'windows'"
                            :class="activeOs === 'windows' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-[8px] text-[11px] transition-all">
                            Windows
                        </button>
                        <button
                            type="button"
                            @click="activeOs = 'macos'"
                            :class="activeOs === 'macos' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-[8px] text-[11px] transition-all">
                            macOS
                        </button>
                        <button
                            type="button"
                            @click="activeOs = 'linux'"
                            :class="activeOs === 'linux' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-[8px] text-[11px] transition-all">
                            Linux
                        </button>
                    </div>
                </div>
            </div>

            {{-- Segmented Control Provider Tabs (8 Providers) --}}
            <div class="flex items-center gap-1.5 p-1.5 rounded-[16px] bg-neutral-100 dark:bg-neutral-800/80 text-xs font-medium overflow-x-auto no-scrollbar">
                <button
                    type="button"
                    @click="activeProviderTab = 'claude'"
                    :class="activeProviderTab === 'claude' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="bot" class="w-4 h-4 text-amber-500"></i>
                    <span>Claude Desktop</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'claude_code'"
                    :class="activeProviderTab === 'claude_code' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="terminal" class="w-4 h-4 text-neutral-700 dark:text-neutral-300"></i>
                    <span>Claude Code CLI</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'cursor'"
                    :class="activeProviderTab === 'cursor' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="code-2" class="w-4 h-4 text-blue-500"></i>
                    <span>Cursor & Antigravity</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'chatgpt'"
                    :class="activeProviderTab === 'chatgpt' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-emerald-500"></i>
                    <span>ChatGPT (Custom Actions)</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'gemini'"
                    :class="activeProviderTab === 'gemini' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="cpu" class="w-4 h-4 text-purple-500"></i>
                    <span>Google Gemini</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'ollama'"
                    :class="activeProviderTab === 'ollama' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="server" class="w-4 h-4 text-sky-500"></i>
                    <span>Local / Ollama</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'n8n'"
                    :class="activeProviderTab === 'n8n' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="git-branch" class="w-4 h-4 text-rose-500"></i>
                    <span>n8n & Dify Automation</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'langchain'"
                    :class="activeProviderTab === 'langchain' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="file-code" class="w-4 h-4 text-indigo-500"></i>
                    <span>LangChain & Python</span>
                </button>
            </div>

            {{-- Tab Contents Detail per Provider --}}
            <div class="mt-4">

                {{-- 1. Claude Desktop --}}
                <div x-show="activeProviderTab === 'claude'" class="space-y-5">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-[8px] bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-semibold">
                            Desktop Application • Local Stdio Transport
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Kompatibel: Windows, macOS, Linux</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Step 1 --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center">1</span>
                                <button
                                    type="button"
                                    @click="copyText(getClaudePath(), 'Path berkas tersalin!')"
                                    class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                    <i data-lucide="copy" class="w-3 h-3"></i>
                                    <span>Salin Path</span>
                                </button>
                            </div>
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Buka Berkas Konfigurasi</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                Buka aplikasi Claude &rarr; Settings &rarr; Developer &rarr; Edit Config, atau buka langsung file berikut:
                            </p>
                            <div class="p-2 rounded-[8px] bg-neutral-200/60 dark:bg-neutral-800 font-mono text-[11px] text-neutral-800 dark:text-neutral-200 break-all select-all">
                                <span x-text="getClaudePath()"></span>
                            </div>
                        </div>

                        {{-- Step 2 --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center">2</span>
                                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">Auto-filled with your token</span>
                            </div>
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Tempelkan Konfigurasi JSON</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                Sisipkan blok <code class="font-mono text-[11px] bg-neutral-200 dark:bg-neutral-800 px-1 py-0.5 rounded">mcpServers</code> ke dalam file konfigurasi Claude Anda.
                            </p>
                            <div class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                Pastikan CLI PHP terdaftar di sistem PATH Anda (misal via Laragon atau PHP Windows).
                            </div>
                        </div>
                    </div>

                    {{-- Live Code Box --}}
                    <div class="relative">
                        <div class="flex items-center justify-between px-4 py-2.5 rounded-t-[16px] bg-neutral-800 text-neutral-300 text-xs font-mono border-b border-neutral-700">
                            <span>claude_desktop_config.json</span>
                            <button
                                type="button"
                                @click="copyText(getClaudeConfig(), 'Konfigurasi Claude Desktop tersalin!')"
                                class="px-3 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span>{{ __('mcp.btn_copy_config') }}</span>
                            </button>
                        </div>
                        <pre class="p-4 rounded-b-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed select-all"><code x-text="getClaudeConfig()"></code></pre>
                    </div>

                    {{-- Step 3 & 4 --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        <div class="p-4 rounded-[16px] bg-blue-50/60 dark:bg-blue-950/30 border border-blue-500/20 space-y-1.5">
                            <div class="flex items-center gap-2 text-blue-700 dark:text-blue-300 font-semibold text-xs">
                                <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">3</span>
                                <span>Restart Aplikasi Claude</span>
                            </div>
                            <p class="text-[11px] text-blue-800/80 dark:text-blue-200/80 leading-relaxed">
                                Tutup aplikasi Claude Desktop sepenuhnya (keluar dari tray/taskbar), lalu buka kembali agar file konfigurasi dimuat ulang.
                            </p>
                        </div>

                        <div class="p-4 rounded-[16px] bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-500/20 space-y-1.5">
                            <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300 font-semibold text-xs">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] flex items-center justify-center font-bold">4</span>
                                <span>Verifikasi Ikon Palu (Tools)</span>
                            </div>
                            <p class="text-[11px] text-emerald-800/80 dark:text-emerald-200/80 leading-relaxed">
                                Buka obrolan baru di Claude. Anda akan melihat ikon palu (hammer) di pojok kanan bawah dengan label <strong>cooca-erp (10 tools)</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 2. Claude Code CLI --}}
                <div x-show="activeProviderTab === 'claude_code'" class="space-y-5" style="display: none;">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-[8px] bg-neutral-500/10 text-neutral-800 dark:text-neutral-200 text-xs font-semibold">
                            Terminal CLI Agent • Official Anthropic Research
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Jalankan langsung di terminal proyek Anda</span>
                    </div>

                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                        <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Daftarkan MCP Server ke Claude Code dalam 1 Baris:</h4>
                        <div class="relative">
                            <pre class="p-3.5 rounded-[12px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed select-all"><code x-text="getClaudeCodeCmd()"></code></pre>
                            <button
                                type="button"
                                @click="copyText(getClaudeCodeCmd(), 'Perintah CLI tersalin!')"
                                class="absolute top-2.5 right-2.5 px-3 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span>Salin Perintah</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="p-4 rounded-[16px] border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <div class="font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="list-checks" class="w-4 h-4 text-emerald-500"></i>
                                <span>Verifikasi Server Terdaftar</span>
                            </div>
                            <pre class="p-2.5 rounded-[8px] bg-neutral-100 dark:bg-neutral-900 font-mono text-[11px] text-neutral-800 dark:text-neutral-200">claude mcp list</pre>
                            <p class="text-[11px] text-neutral-500">Akan menampilkan <code class="font-mono">cooca-erp: Connected (10 tools)</code>.</p>
                        </div>

                        <div class="p-4 rounded-[16px] border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <div class="font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="message-square" class="w-4 h-4 text-blue-500"></i>
                                <span>Contoh Perintah Langsung</span>
                            </div>
                            <pre class="p-2.5 rounded-[8px] bg-neutral-100 dark:bg-neutral-900 font-mono text-[11px] text-neutral-800 dark:text-neutral-200">claude "Berapa posisi kas dan rekening bank toko saya?"</pre>
                            <p class="text-[11px] text-neutral-500">Claude Code akan memanggil tool MCP COOCA secara otonom.</p>
                        </div>
                    </div>
                </div>

                {{-- 3. Cursor & Antigravity IDE --}}
                <div x-show="activeProviderTab === 'cursor'" class="space-y-5" style="display: none;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-[8px] bg-blue-500/10 text-blue-600 dark:text-blue-400 text-xs font-semibold">
                                AI Code Editor • Remote SSE & Stdio
                            </span>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Kompatibel: Cursor, Antigravity, VS Code MCP</span>
                        </div>

                        {{-- Sub-toggle Stdio vs SSE --}}
                        <div class="inline-flex p-1 rounded-[10px] bg-neutral-100 dark:bg-neutral-800 text-[11px] font-medium">
                            <button
                                type="button"
                                @click="cursorMode = 'sse'"
                                :class="cursorMode === 'sse' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500'"
                                class="px-2.5 py-1 rounded-[7px] transition-all">
                                Remote SSE (Rekomendasi)
                            </button>
                            <button
                                type="button"
                                @click="cursorMode = 'stdio'"
                                :class="cursorMode === 'stdio' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500'"
                                class="px-2.5 py-1 rounded-[7px] transition-all">
                                Local Stdio Pipe
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                            <span class="w-5 h-5 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-[10px] flex items-center justify-center font-bold">1</span>
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Buka Pengaturan MCP</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                Buka <strong>Cursor Settings &rarr; Features &rarr; MCP</strong> atau buat berkas <code class="font-mono text-[10px] bg-neutral-200 dark:bg-neutral-800 px-1 py-0.5 rounded">.cursor/mcp.json</code> di root proyek Anda.
                            </p>
                        </div>
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                            <span class="w-5 h-5 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-[10px] flex items-center justify-center font-bold">2</span>
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Tempelkan Konfigurasi</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                Salin blok JSON di bawah. Token otentikasi telah terisi secara otomatis sesuai token yang Anda pilih.
                            </p>
                        </div>
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                            <span class="w-5 h-5 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-[10px] flex items-center justify-center font-bold">3</span>
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Cek Titik Status Hijau</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                Klik tombol <strong>Refresh</strong> di Cursor. Pastikan titik status server <code class="font-mono text-[10px]">cooca-erp</code> berwarna hijau.
                            </p>
                        </div>
                    </div>

                    {{-- Live Code Box Cursor --}}
                    <div class="relative">
                        <div class="flex items-center justify-between px-4 py-2.5 rounded-t-[16px] bg-neutral-800 text-neutral-300 text-xs font-mono border-b border-neutral-700">
                            <span>.cursor/mcp.json</span>
                            <button
                                type="button"
                                @click="copyText(cursorMode === 'sse' ? getCursorSseConfig() : getCursorStdioConfig(), 'Konfigurasi Cursor tersalin!')"
                                class="px-3 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span>{{ __('mcp.btn_copy_config') }}</span>
                            </button>
                        </div>
                        <pre class="p-4 rounded-b-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed select-all"><code x-text="cursorMode === 'sse' ? getCursorSseConfig() : getCursorStdioConfig()"></code></pre>
                    </div>
                </div>

                {{-- 4. ChatGPT (Custom Actions) --}}
                <div x-show="activeProviderTab === 'chatgpt'" class="space-y-5" style="display: none;">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                            ChatGPT Web & Mobile • Dynamic OpenAPI 3.1.0 • No-Code Action
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Dukungan untuk ChatGPT Plus / Team / Enterprise</span>
                    </div>

                    <div class="space-y-3">
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Langkah 1: Buka GPT Builder di ChatGPT</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                Kunjungi <strong>chatgpt.com &rarr; Explore GPTs &rarr; Create a GPT</strong> (atau edit Custom GPT Anda). Buka tab <strong>Configure</strong>, gulir ke bagian paling bawah, lalu klik <strong>Create new action</strong>.
                            </p>
                        </div>

                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Langkah 2: Impor Skema OpenAPI 3.1 dari URL</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                Pada kolom Schema, klik <strong>Import from URL</strong>, tempelkan URL endpoint di bawah, lalu klik <strong>Import</strong>. Seluruh 10 tool COOCA akan otomatis terkonversi menjadi Actions ChatGPT:
                            </p>
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $openApiEndpoint }}"
                                    class="flex-1 font-mono text-xs bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white px-3.5 py-2 rounded-[10px] border border-neutral-200 dark:border-neutral-700 select-all" />
                                <button
                                    type="button"
                                    @click="copyText('{{ $openApiEndpoint }}', 'URL OpenAPI tersalin!')"
                                    class="px-4 py-2 rounded-[10px] bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-medium flex items-center gap-1.5 transition-colors">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    <span>Salin URL</span>
                                </button>
                            </div>
                        </div>

                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Langkah 3: Konfigurasi Authentication</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                Klik ikon gear di sebelah <strong>Authentication</strong>, lalu pilih:
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                <div class="p-2.5 rounded-[10px] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800">
                                    <div class="text-[10px] text-neutral-400 uppercase">Authentication Type</div>
                                    <div class="font-semibold text-neutral-900 dark:text-white mt-0.5">API Key</div>
                                </div>
                                <div class="p-2.5 rounded-[10px] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800">
                                    <div class="text-[10px] text-neutral-400 uppercase">Auth Type</div>
                                    <div class="font-semibold text-neutral-900 dark:text-white mt-0.5">Bearer</div>
                                </div>
                                <div class="p-2.5 rounded-[10px] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                                    <div>
                                        <div class="text-[10px] text-neutral-400 uppercase">API Key Value</div>
                                        <div class="font-mono text-[11px] text-neutral-900 dark:text-white mt-0.5">Token MCP Anda</div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="copyText(getToken(), 'Token tersalin!')"
                                        class="text-blue-600 dark:text-blue-400 hover:underline text-[11px]">
                                        Salin
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-[16px] bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-500/20 space-y-1">
                            <h4 class="text-xs font-semibold text-emerald-800 dark:text-emerald-200">Langkah 4: Uji Coba di Panel Preview</h4>
                            <p class="text-[11px] text-emerald-700 dark:text-emerald-300">
                                Ketik pada chat preview: <em>"Tolong buatkan ringkasan laba rugi toko saya bulan ini"</em>. ChatGPT akan meminta konfirmasi izin akses ke server COOCA (klik <strong>Always Allow</strong>).
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 5. Google Gemini --}}
                <div x-show="activeProviderTab === 'gemini'" class="space-y-5" style="display: none;">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-[8px] bg-purple-500/10 text-purple-600 dark:text-purple-400 text-xs font-semibold">
                            Google GenAI / Vertex AI • Python SDK & Direct REST Bridge
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Kompatibel: Gemini 1.5 Pro, Flash, Gemini 2.0</span>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Cara Kerja REST Direct Bridge COOCA:</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                Google Gemini dapat memanggil 10 tools COOCA secara instan melalui endpoint REST Bridge:
                                <code class="font-mono text-neutral-800 dark:text-neutral-200 bg-neutral-200 dark:bg-neutral-800 px-1 py-0.5 rounded">POST {{ $apiBaseUrl }}/tools/{nama_tool}/execute</code> dengan header otentikasi Bearer Token.
                            </p>
                        </div>

                        {{-- Python SDK Code Box --}}
                        <div class="relative">
                            <div class="flex items-center justify-between px-4 py-2.5 rounded-t-[16px] bg-neutral-800 text-neutral-300 text-xs font-mono border-b border-neutral-700">
                                <span>gemini_cooca_bridge.py</span>
                                <button
                                    type="button"
                                    @click="copyText(getGeminiPythonCode(), 'Script Python tersalin!')"
                                    class="px-3 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    <span>Salin Kode Python</span>
                                </button>
                            </div>
                            <pre class="p-4 rounded-b-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed select-all"><code x-text="getGeminiPythonCode()"></code></pre>
                        </div>

                        {{-- cURL Quick Test Box --}}
                        <div class="relative">
                            <div class="flex items-center justify-between px-4 py-2 rounded-t-[14px] bg-neutral-800/80 text-neutral-300 text-xs font-mono border-b border-neutral-700">
                                <span>cURL Terminal Test (Langsung dari Terminal)</span>
                                <button
                                    type="button"
                                    @click="copyText(getGeminiCurl(), 'Perintah cURL tersalin!')"
                                    class="px-2.5 py-0.5 rounded-[6px] bg-white/10 hover:bg-white/20 text-white text-[11px] flex items-center gap-1">
                                    <i data-lucide="copy" class="w-3 h-3"></i>
                                    <span>Salin cURL</span>
                                </button>
                            </div>
                            <pre class="p-3.5 rounded-b-[14px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto select-all"><code x-text="getGeminiCurl()"></code></pre>
                        </div>
                    </div>
                </div>

                {{-- 6. Local / Ollama --}}
                <div x-show="activeProviderTab === 'ollama'" class="space-y-5" style="display: none;">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-[8px] bg-sky-500/10 text-sky-600 dark:text-sky-400 text-xs font-semibold">
                            100% Offline / Local AI • MCP Inspector & Stdio
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Kompatibel: Ollama, Open WebUI, LM Studio, Continue.dev</span>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Metode 1: Uji Coba Cepat dengan MCP Inspector Resmi</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                Jalankan perintah berikut di terminal Anda untuk membuka dashboard inspeksi dan debugging MCP resmi di browser (<code class="font-mono text-[11px]">http://localhost:5173</code>):
                            </p>
                            <div class="relative">
                                <pre class="p-3.5 rounded-[12px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto select-all"><code x-text="getOllamaInspectorCmd()"></code></pre>
                                <button
                                    type="button"
                                    @click="copyText(getOllamaInspectorCmd(), 'Perintah Inspector tersalin!')"
                                    class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1">
                                    <i data-lucide="copy" class="w-3 h-3"></i>
                                    <span>Salin</span>
                                </button>
                            </div>
                        </div>

                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">Metode 2: Integrasi ke Open WebUI / Continue.dev</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                Tambahkan server MCP Remote SSE pada konfigurasi Continue.dev atau Open WebUI Tools:
                            </p>
                            <div class="p-3 rounded-[10px] bg-neutral-100 dark:bg-neutral-900 font-mono text-xs text-neutral-800 dark:text-neutral-200 select-all">
                                URL: <span class="text-blue-600 dark:text-blue-400">{{ $sseEndpoint }}</span><br>
                                Header: Authorization: Bearer <span x-text="getToken()"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 7. n8n & Dify Automation --}}
                <div x-show="activeProviderTab === 'n8n'" class="space-y-5" style="display: none;">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-[8px] bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-semibold">
                            Enterprise Workflow Automation • No-Code / Low-Code
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Dukungan: n8n, Dify.ai, Make.com, Flowise</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4.5 rounded-[18px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                            <div class="flex items-center gap-2 font-semibold text-xs text-neutral-900 dark:text-white">
                                <i data-lucide="workflow" class="w-4 h-4 text-rose-500"></i>
                                <span>Integrasi di n8n Workflow</span>
                            </div>
                            <ol class="list-decimal list-inside text-[11px] text-neutral-600 dark:text-neutral-300 space-y-2 leading-relaxed">
                                <li>Tambahkan node <strong>HTTP Request</strong> di workflow n8n Anda.</li>
                                <li>Atur Method: <strong>POST</strong>.</li>
                                <li>URL: <code class="font-mono text-[10px] bg-neutral-200 dark:bg-neutral-800 px-1 py-0.5 rounded">{{ $apiBaseUrl }}/tools/finance_record_expense/execute</code></li>
                                <li>Headers: <code class="font-mono text-[10px] bg-neutral-200 dark:bg-neutral-800 px-1 py-0.5 rounded">Authorization: Bearer <span x-text="getToken()"></span></code></li>
                                <li>Body: Masukkan parameter JSON tool (contoh: amount, category, description).</li>
                            </ol>
                        </div>

                        <div class="p-4.5 rounded-[18px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                            <div class="flex items-center gap-2 font-semibold text-xs text-neutral-900 dark:text-white">
                                <i data-lucide="layers" class="w-4 h-4 text-blue-500"></i>
                                <span>Integrasi di Dify.ai Agent</span>
                            </div>
                            <ol class="list-decimal list-inside text-[11px] text-neutral-600 dark:text-neutral-300 space-y-2 leading-relaxed">
                                <li>Buka Dify.ai &rarr; menu <strong>Tools</strong> &rarr; <strong>Custom Tools</strong>.</li>
                                <li>Klik <strong>Import from OpenAPI</strong>.</li>
                                <li>Tempelkan URL OpenAPI COOCA:
                                    <div class="mt-1 flex items-center gap-1">
                                        <input type="text" readonly value="{{ $openApiEndpoint }}" class="w-full text-[10px] font-mono px-2 py-1 rounded bg-neutral-200/60 dark:bg-neutral-800" />
                                        <button type="button" @click="copyText('{{ $openApiEndpoint }}')" class="p-1 text-blue-600 dark:text-blue-400"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                    </div>
                                </li>
                                <li>Atur Authorization: <strong>API Key</strong> &rarr; <strong>Bearer</strong> &rarr; Tempelkan token MCP Anda.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                {{-- 8. LangChain & Python Agents --}}
                <div x-show="activeProviderTab === 'langchain'" class="space-y-5" style="display: none;">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-[8px] bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-semibold">
                            Python AI Framework • Agentic Tool Calling
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Kompatibel: LangChain, LlamaIndex, CrewAI, AutoGen</span>
                    </div>

                    <div class="relative">
                        <div class="flex items-center justify-between px-4 py-2.5 rounded-t-[16px] bg-neutral-800 text-neutral-300 text-xs font-mono border-b border-neutral-700">
                            <span>langchain_cooca_agent.py</span>
                            <button
                                type="button"
                                @click="copyText(getLangChainCode(), 'Kode LangChain tersalin!')"
                                class="px-3 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span>Salin Kode Python</span>
                            </button>
                        </div>
                        <pre class="p-4 rounded-b-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed select-all"><code x-text="getLangChainCode()"></code></pre>
                    </div>
                </div>

            </div>
        </div>

        {{-- KARTU 3B: Katalog 10 Domain Tools & Rekomendasi Prompt AI --}}
        <div class="lg:col-span-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 sm:p-7 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="wrench" class="w-4 h-4 text-blue-500"></i>
                        <span>{{ __('mcp.tools_catalog_title') }}</span>
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ __('mcp.tools_catalog_desc') }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-mono font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                    10 Tools Terverifikasi
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Tool 1 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">finance_record_expense</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">mcp:expenses:write</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Mencatat pengeluaran operasional toko dari struk/receipt dengan auto-journal akuntansi dan mutasi kas keluar.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Catat pengeluaran beli kertas struk 45.000 via kas tunai"</span>
                        <button
                            type="button"
                            @click="copyText('Catat pengeluaran beli kertas struk 45.000 via kas tunai', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>

                {{-- Tool 2 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">finance_get_cash_and_bank_balances</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">mcp:finance:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Membaca posisi likuiditas uang tunai toko dan saldo rekening bank aktif secara real-time.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Berapa total saldo kas toko dan rekening bank saat ini?"</span>
                        <button
                            type="button"
                            @click="copyText('Berapa total saldo kas toko dan rekening bank aktif saat ini?', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>

                {{-- Tool 3 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">inventory_create_product</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">mcp:products:write</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Mendaftarkan produk baru ke katalog barang dengan modal HPP, harga jual, barcode, dan stok awal.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Daftarkan produk Kopi Susu Aren, HPP 8000, jual 18000, stok 50"</span>
                        <button
                            type="button"
                            @click="copyText('Daftarkan produk baru Kopi Susu Aren dengan modal HPP 8000 dan harga jual 18000, stok awal 50 pcs', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>

                {{-- Tool 4 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-amber-600 dark:text-amber-400">inventory_check_stock</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400">mcp:products:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Memeriksa jumlah stok produk tertentu atau mendeteksi barang kritis yang stoknya di bawah batas reorder point.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Cek barang apa saja yang stoknya kritis atau habis"</span>
                        <button
                            type="button"
                            @click="copyText('Cek barang apa saja yang stoknya kritis atau hampir habis di toko', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>

                {{-- Tool 5 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-rose-600 dark:text-rose-400">social_schedule_post</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400">mcp:social:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Menjadwalkan penerbitan materi promosi ke Instagram, TikTok, Facebook Page, atau X/Twitter.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Jadwalkan postingan promo kopi ke Instagram besok jam 10 pagi"</span>
                        <button
                            type="button"
                            @click="copyText('Jadwalkan postingan promo kopi Beli 1 Gratis 1 ke Instagram besok jam 10 pagi', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>

                {{-- Tool 6 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-purple-600 dark:text-purple-400">report_get_profit_loss</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400">mcp:reports:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Menghitung ringkasan laba rugi bisnis: total omzet, HPP, laba kotor, beban operasional, dan margin laba bersih.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Hitung ringkasan laba rugi bisnis bulan berjalan"</span>
                        <button
                            type="button"
                            @click="copyText('Hitung ringkasan laba rugi bisnis untuk bulan berjalan', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>

                {{-- Tool 7 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-teal-600 dark:text-teal-400">analytics_get_sales_forecast</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-teal-500/10 text-teal-600 dark:text-teal-400">mcp:reports:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Memproyeksikan tren penjualan masa depan dan estimasi omzet berdasarkan data historis transaksi.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Berapa estimasi proyeksi omzet 7 hari ke depan?"</span>
                        <button
                            type="button"
                            @click="copyText('Berapa estimasi proyeksi omzet penjualan untuk 7 hari ke depan?', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>

                {{-- Tool 8 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-sky-600 dark:text-sky-400">crm_search_customer</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-sky-500/10 text-sky-600 dark:text-sky-400">mcp:crm:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Mencari profil pelanggan setia, nomor telepon, tier keanggotaan, dan riwayat belanja.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Cari data pelanggan dengan nomor HP 081234567890"</span>
                        <button
                            type="button"
                            @click="copyText('Cari data pelanggan dengan nomor HP 081234567890', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>

                {{-- Tool 9 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">whatsapp_send_notification</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">mcp:whatsapp:send</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Mengirimkan pesan notifikasi resmi via WhatsApp Cloud API Meta kepada nomor telepon tujuan.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Kirim WA ke 081234567890 bahwa barang sudah siap"</span>
                        <button
                            type="button"
                            @click="copyText('Kirim pesan WhatsApp ke 081234567890 bahwa pesanan sudah selesai dan siap diambil', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>

                {{-- Tool 10 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-pink-600 dark:text-pink-400">social_get_insights</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-pink-500/10 text-pink-600 dark:text-pink-400">mcp:social:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            Menganalisis performa interaksi media sosial dan ringkasan efektivitas materi pemasaran bisnis.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"Bagaimana statistik performa postingan medsos toko kita?"</span>
                        <button
                            type="button"
                            @click="copyText('Bagaimana statistik performa postingan media sosial toko kita minggu ini?', 'Prompt tersalin!')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            Salin
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 3C: Pusat Solusi & Pemecahan Kendala (Troubleshooting) --}}
        <div class="lg:col-span-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 sm:p-7 shadow-sm space-y-5">
            <div class="flex items-center gap-2 pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <i data-lucide="help-circle" class="w-4 h-4 text-amber-500"></i>
                <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                    {{ __('mcp.troubleshooting_title') }}
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>Tool tidak muncul di Claude?</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        Pastikan Claude Desktop telah ditutup sepenuhnya (Close via Tray) lalu buka kembali. Pastikan path PHP valid di sistem environment Anda.
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>Error -32000 (Unauthorized)?</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        Token yang dimasukkan salah, telah dicabut, atau kedaluwarsa. Terbitkan token baru di bagian atas halaman ini dan perbarui konfigurasi.
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        <span>Error -32001 (Lacks Ability)?</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        Token Anda tidak memiliki izin untuk tool tersebut. Buat token baru dengan mencentang izin spesifik atau pilih <strong>Semua Akses (*)</strong>.
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Apakah Data Toko Aman?</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        Sangat aman. Setiap token terenkripsi SHA-256 dan terisolasi secara ketat pada bisnis Anda. AI tidak dapat mengakses data tenant lain.
                    </p>
                </div>
            </div>
        </div>

        {{-- KARTU 4: Live Activity & Audit Log Feed (12-Kolom) --}}
        <div class="lg:col-span-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                        {{ __('mcp.logs_title') }}
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ __('mcp.logs_desc') }}
                    </p>
                </div>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    Menampilkan 15 aktivitas terakhir
                </span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-neutral-400 dark:text-neutral-500 border-b border-neutral-100 dark:border-neutral-800/80">
                            <th class="py-2.5 font-medium">{{ __('mcp.col_time') }}</th>
                            <th class="py-2.5 font-medium">{{ __('mcp.col_tool') }}</th>
                            <th class="py-2.5 font-medium">{{ __('mcp.col_client') }}</th>
                            <th class="py-2.5 font-medium">{{ __('mcp.col_duration') }}</th>
                            <th class="py-2.5 font-medium">{{ __('mcp.col_status') }}</th>
                            <th class="py-2.5 font-medium text-right">{{ __('mcp.col_ip') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                        @forelse($logs as $log)
                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors">
                            <td class="py-3 text-neutral-500 dark:text-neutral-400 tabular-nums">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-3 font-mono font-medium text-neutral-900 dark:text-neutral-200">
                                {{ $log->tool_name }}
                            </td>
                            <td class="py-3 text-neutral-600 dark:text-neutral-300">
                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] bg-neutral-100 dark:bg-neutral-800">
                                    {{ ucfirst($log->client_provider) }}
                                </span>
                            </td>
                            <td class="py-3 text-neutral-500 dark:text-neutral-400 tabular-nums">
                                {{ $log->execution_time_ms }} ms
                            </td>
                            <td class="py-3">
                                @if($log->response_status === 'success')
                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium text-[11px]">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        {{ __('mcp.status_success') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-rose-500 font-medium text-[11px]" title="{{ $log->error_message }}">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ __('mcp.status_error') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 text-right text-neutral-400 dark:text-neutral-500 font-mono text-[11px]">
                                {{ $log->ip_address ?: '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                                {{ __('mcp.empty_logs') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>

    </div>

    {{-- MODAL SHEET: Buat Token Baru (Bento Apple HIG) --}}
    <div
        x-show="openCreateModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
        style="display: none;"
        @keydown.escape.window="openCreateModal = false">

        <div
            @click.outside="openCreateModal = false"
            class="w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800">
                <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                    {{ __('mcp.modal_create_title') }}
                </h3>
                <button
                    type="button"
                    @click="openCreateModal = false"
                    class="p-1 rounded-full text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('settings.integrations.mcp.tokens.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        {{ __('mcp.input_token_name') }} <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="{{ __('mcp.input_token_name_ph') }}"
                        class="w-full min-h-[44px] text-[16px] sm:text-[15px] px-3.5 py-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            {{ __('mcp.input_provider') }}
                        </label>
                        <select
                            name="provider_hint"
                            class="w-full min-h-[44px] text-[16px] sm:text-[15px] px-3 py-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-white focus:outline-none">
                            <option value="all">Semua Provider (Universal)</option>
                            <option value="claude">Claude Desktop</option>
                            <option value="cursor">Cursor / Antigravity</option>
                            <option value="openai">ChatGPT / OpenAI</option>
                            <option value="gemini">Google Gemini</option>
                            <option value="ollama">Local / Ollama</option>
                            <option value="langchain">LangChain / n8n</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            {{ __('mcp.input_expires') }}
                        </label>
                        <select
                            name="expires_in"
                            class="w-full min-h-[44px] text-[16px] sm:text-[15px] px-3 py-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-white focus:outline-none">
                            <option value="never">{{ __('mcp.expires_never') }}</option>
                            <option value="30_days">{{ __('mcp.expires_30_days') }}</option>
                            <option value="90_days">{{ __('mcp.expires_90_days') }}</option>
                            <option value="1_year">{{ __('mcp.expires_1_year') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('mcp.permissions_title') }}
                    </label>

                    <div class="space-y-2 p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-h-56 overflow-y-auto">
                        <label class="flex items-center gap-2.5 text-xs text-neutral-900 dark:text-white font-medium pb-2 border-b border-neutral-200 dark:border-neutral-800 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="*" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_all') }}</span>
                        </label>

                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:expenses:write" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_finance_write') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:finance:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_finance_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:products:manage" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_products_manage') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:products:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_products_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:social:manage" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_social_manage') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:social:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_social_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:reports:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_reports_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:analytics:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_analytics_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:crm:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_crm_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:whatsapp:send" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_whatsapp_send') }}</span>
                        </label>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="openCreateModal = false"
                        class="px-4 py-2.5 min-h-[44px] rounded-[12px] text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 min-h-[44px] rounded-[12px] bg-neutral-900 hover:bg-black dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-100 text-white text-xs font-semibold shadow-sm transition-all">
                        Terbitkan Kunci Akses
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toast Notification (Bento Apple HIG Floating Pill) --}}
    <div
        x-show="toastVisible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-neutral-900/95 dark:bg-white/95 text-white dark:text-neutral-900 shadow-2xl backdrop-blur-md text-xs font-medium border border-white/10 dark:border-black/10 pointer-events-none"
        style="display: none;">
        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 dark:text-emerald-600"></i>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection

