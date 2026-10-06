@extends('layouts.app')

@section('title', __('mcp.page_title'))

@section('content')
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

        {{-- KARTU 3: Universal 1-Click Multi-Provider Setup Hub (12-Kolom) --}}
        <div class="lg:col-span-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                        {{ __('mcp.setup_hub_title') }}
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ __('mcp.setup_hub_desc') }}
                    </p>
                </div>

                {{-- Segmented Control Apple HIG --}}
                <div class="inline-flex p-1 rounded-[14px] bg-neutral-100 dark:bg-neutral-800/80 text-xs font-medium overflow-x-auto no-scrollbar">
                    <button
                        type="button"
                        @click="activeProviderTab = 'claude'"
                        :class="activeProviderTab === 'claude' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[10px] transition-all whitespace-nowrap">
                        Claude Desktop
                    </button>
                    <button
                        type="button"
                        @click="activeProviderTab = 'cursor'"
                        :class="activeProviderTab === 'cursor' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[10px] transition-all whitespace-nowrap">
                        Cursor / Antigravity
                    </button>
                    <button
                        type="button"
                        @click="activeProviderTab = 'chatgpt'"
                        :class="activeProviderTab === 'chatgpt' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[10px] transition-all whitespace-nowrap">
                        ChatGPT (Custom Actions)
                    </button>
                    <button
                        type="button"
                        @click="activeProviderTab = 'gemini'"
                        :class="activeProviderTab === 'gemini' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[10px] transition-all whitespace-nowrap">
                        Google Gemini
                    </button>
                    <button
                        type="button"
                        @click="activeProviderTab = 'ollama'"
                        :class="activeProviderTab === 'ollama' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[10px] transition-all whitespace-nowrap">
                        Local / Ollama
                    </button>
                    <button
                        type="button"
                        @click="activeProviderTab = 'langchain'"
                        :class="activeProviderTab === 'langchain' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[10px] transition-all whitespace-nowrap">
                        LangChain / n8n
                    </button>
                </div>
            </div>

            {{-- Tab Contents --}}
            <div class="mt-5">
                {{-- 1. Claude Desktop --}}
                <div x-show="activeProviderTab === 'claude'" class="space-y-3">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Tambahkan blok konfigurasi ini ke file <code class="px-1.5 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 font-mono">claude_desktop_config.json</code> di laptop Anda:
                    </p>
                    <div class="relative">
                        <pre class="p-4 rounded-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed"><code>{
  "mcpServers": {
    "cooca-erp": {
      "command": "php",
      "args": ["artisan", "mcp:serve", "--token=YOUR_MCP_TOKEN"],
      "cwd": "{{ base_path() }}"
    }
  }
}</code></pre>
                        <button
                            type="button"
                            @click="copyText(getClaudeConfig())"
                            class="absolute top-3 right-3 px-3 py-1.5 rounded-[10px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            <span>{{ __('mcp.btn_copy_config') }}</span>
                        </button>
                    </div>
                </div>

                {{-- 2. Cursor / Antigravity --}}
                <div x-show="activeProviderTab === 'cursor'" class="space-y-3" style="display: none;">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Tambahkan endpoint Remote SSE ini ke pengaturan MCP Cursor (<code class="px-1.5 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 font-mono">.cursor/mcp.json</code>) atau Antigravity IDE:
                    </p>
                    <div class="relative">
                        <pre class="p-4 rounded-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed"><code>{
  "mcpServers": {
    "cooca-erp": {
      "url": "{{ $sseEndpoint }}",
      "headers": {
        "Authorization": "Bearer YOUR_MCP_TOKEN"
      }
    }
  }
}</code></pre>
                        <button
                            type="button"
                            @click="copyText(getCursorConfig())"
                            class="absolute top-3 right-3 px-3 py-1.5 rounded-[10px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            <span>{{ __('mcp.btn_copy_config') }}</span>
                        </button>
                    </div>
                </div>

                {{-- 3. ChatGPT (Custom Actions) --}}
                <div x-show="activeProviderTab === 'chatgpt'" class="space-y-3" style="display: none;">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Pada Custom GPT Builder (Configure &rarr; Actions), pilih <strong>Import from URL</strong> dan masukkan URL OpenAPI berikut:
                    </p>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            readonly
                            value="{{ $openApiEndpoint }}"
                            class="flex-1 font-mono text-xs bg-neutral-50 dark:bg-neutral-900 text-neutral-900 dark:text-white px-3 py-2.5 rounded-[12px] border border-neutral-200 dark:border-neutral-800 select-all" />
                        <button
                            type="button"
                            @click="copyText('{{ $openApiEndpoint }}')"
                            class="px-4 py-2.5 rounded-[12px] bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-medium flex items-center gap-1.5 transition-colors">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            <span>Salin URL OpenAPI</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-neutral-400 dark:text-neutral-500">
                        Di tab Authentication ChatGPT, pilih <strong>API Key</strong> &rarr; <strong>Bearer</strong> dan tempelkan token MCP Anda.
                    </p>
                </div>

                {{-- 4. Google Gemini --}}
                <div x-show="activeProviderTab === 'gemini'" class="space-y-3" style="display: none;">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Gunakan Python SDK Google GenAI atau Vertex AI dengan REST Tool Bridge COOCA:
                    </p>
                    <div class="relative">
                        <pre class="p-4 rounded-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed"><code>import google.generativeai as genai
import requests

COOCA_TOKEN = "YOUR_MCP_TOKEN"
API_BASE = "{{ $apiBaseUrl }}"

def call_cooca_tool(tool_name: str, args: dict):
    resp = requests.post(
        f"{API_BASE}/tools/{tool_name}/execute",
        headers={"Authorization": f"Bearer {COOCA_TOKEN}"},
        json=args
    )
    return resp.json()</code></pre>
                    </div>
                </div>

                {{-- 5. Local / Ollama --}}
                <div x-show="activeProviderTab === 'ollama'" class="space-y-3" style="display: none;">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Jalankan MCP Inspector atau integrasi Ollama Tool Calling via Artisan Stdio Pipe:
                    </p>
                    <div class="relative">
                        <pre class="p-4 rounded-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed"><code># Jalankan MCP Inspector Lokal:
npx @modelcontextprotocol/inspector php artisan mcp:serve --token=YOUR_MCP_TOKEN</code></pre>
                    </div>
                </div>

                {{-- 6. LangChain / n8n --}}
                <div x-show="activeProviderTab === 'langchain'" class="space-y-3" style="display: none;">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Untuk n8n, Flowise, atau LangChain, gunakan <strong>HTTP Request Tool</strong> atau <strong>OpenAPI Tool Node</strong> dengan URL:
                    </p>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            readonly
                            value="{{ $openApiEndpoint }}"
                            class="flex-1 font-mono text-xs bg-neutral-50 dark:bg-neutral-900 text-neutral-900 dark:text-white px-3 py-2.5 rounded-[12px] border border-neutral-200 dark:border-neutral-800 select-all" />
                        <button
                            type="button"
                            @click="copyText('{{ $openApiEndpoint }}')"
                            class="px-4 py-2.5 rounded-[12px] bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-medium flex items-center gap-1.5 transition-colors">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            <span>Salin URL</span>
                        </button>
                    </div>
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

</div>

@push('scripts')
<script>
function mcpSettingsHub() {
    return {
        openCreateModal: false,
        activeProviderTab: 'claude',

        copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert("{{ __('mcp.copied') }}");
            });
        },

        getClaudeConfig() {
            return JSON.stringify({
                "mcpServers": {
                    "cooca-erp": {
                        "command": "php",
                        "args": ["artisan", "mcp:serve", "--token=YOUR_MCP_TOKEN"],
                        "cwd": "{{ base_path() }}"
                    }
                }
            }, null, 2);
        },

        getCursorConfig() {
            return JSON.stringify({
                "mcpServers": {
                    "cooca-erp": {
                        "url": "{{ $sseEndpoint }}",
                        "headers": {
                            "Authorization": "Bearer YOUR_MCP_TOKEN"
                        }
                    }
                }
            }, null, 2);
        }
    };
}
</script>
@endpush
@endsection
