@extends('layouts.app', ['title' => __('ai.providers.title') . ''])

@section('content')
<div class="space-y-6" x-data="aiProvidersApp()">

    <!-- Unified Apple HIG Navigation Hub -->
    @include('app.ai.partials.office_navigation', ['activeOffice' => 'providers'])

    <!-- Toast Notification (Frosted Glass Apple HIG) -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="fixed bottom-6 right-6 z-50 max-w-md w-full shadow-2xl rounded-2xl p-4 border backdrop-blur-xl"
         :class="toast.type === 'success' 
            ? 'bg-emerald-950/90 text-emerald-100 border-emerald-500/30' 
            : 'bg-rose-950/90 text-rose-100 border-rose-500/30'"
         style="display: none;">
        <div class="flex items-start gap-3">
            <div class="p-1 rounded-lg shrink-0 mt-0.5"
                 :class="toast.type === 'success' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300'">
                <template x-if="toast.type === 'success'">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </template>
                <template x-if="toast.type !== 'success'">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </template>
            </div>
            <div class="flex-1 text-xs">
                <div class="font-semibold" x-text="toast.type === 'success' ? '{{ __('ai.alerts.success') }}' : '{{ __('ai.alerts.error') }}'"></div>
                <div class="mt-0.5 text-white/90 leading-relaxed" x-text="toast.message"></div>
            </div>
            <button type="button" @click="toast.show = false" class="text-white/50 hover:text-white transition">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('cooca-ai.index') }}" class="text-xs text-black/50 hover:text-black dark:text-white/50 dark:hover:text-white flex items-center gap-1 transition">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.navigation.back_to_office') }}</span>
                </a>
            </div>
            <h1 class="text-2xl font-bold text-black dark:text-white tracking-tight mt-1">{{ __('ai.providers.title') }}</h1>
            <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">{{ __('ai.providers.subtitle') }}</p>
        </div>
    </div>

    <!-- Security & Encryption Guarantee Banner -->
    <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/10 dark:border-white/10 flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
        </div>
        <div class="space-y-0.5 text-xs text-black/70 dark:text-white/70">
            <div class="font-bold text-black dark:text-white">{{ __('ai.providers.security_title') }}</div>
            <p>{{ __('ai.providers.security_desc') }}</p>
        </div>
    </div>

    <!-- Quick Setup Guide for Boomers / Non-tech users -->
    <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/20 border border-indigo-200/60 dark:border-indigo-500/20 flex items-start gap-3.5">
        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 mt-0.5">
            <i data-lucide="key" class="w-4 h-4"></i>
        </div>
        <div class="space-y-1 text-xs text-slate-700 dark:text-zinc-300">
            <div class="font-bold text-slate-900 dark:text-white">Panduan Penggunaan API Key Mandiri (BYOAI)</div>
            <p class="leading-relaxed">Fitur Tanya AI tidak memerlukan top-up token. Cukup klik tombol <strong>Dapatkan API Key</strong> pada provider pilihan Anda di bawah ini (misal: Google AI Studio atau OpenAI), dapatkan API Key dari situs resmi mereka, lalu salin dan simpan di kolom yang tersedia. Setelah tersimpan, Anda dapat langsung mengobrol dengan tim AI di Virtual Office.</p>
        </div>
    </div>

    @php
        $providerPortals = [
            'openai' => [
                'name' => 'OpenAI Platform',
                'url' => 'https://platform.openai.com/api-keys',
                'desc' => 'Dapatkan API Key di OpenAI Developer Platform',
            ],
            'gemini' => [
                'name' => 'Google AI Studio',
                'url' => 'https://aistudio.google.com/app/apikey',
                'desc' => 'Dapatkan API Key di Google AI Studio (Tersedia Tier Gratis)',
            ],
            'anthropic' => [
                'name' => 'Anthropic Console',
                'url' => 'https://console.anthropic.com/settings/keys',
                'desc' => 'Dapatkan API Key Claude di Anthropic Console',
            ],
            'openrouter' => [
                'name' => 'OpenRouter Keys',
                'url' => 'https://openrouter.ai/keys',
                'desc' => 'Akses multi-model AI global via satu API key OpenRouter',
            ],
        ];
    @endphp

    <!-- Provider Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($catalog as $slug => $item)
            @php
                $config = $configured->get($slug);
                $isConfigured = $config && !empty($config->api_key);
                $status = $config?->status ?? 'untested';
                $portal = $providerPortals[$slug] ?? null;
            @endphp
            <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4 relative flex flex-col justify-between"
                 x-init="initProvider('{{ $slug }}', '{{ $status }}', '{{ addslashes($config?->last_error ?? '') }}', '{{ $config?->tested_at?->format('d M Y H:i') ?? '' }}', {{ json_encode($item['models']) }})">
                
                <div class="space-y-4">
                    <!-- Provider Top -->
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-black dark:text-white">{{ $item['name'] }}</h3>
                                @if($config?->is_default)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">DEFAULT</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-black/40 dark:text-white/40 font-mono mt-0.5">{{ strtoupper($slug) }} API</div>
                        </div>

                        <!-- Status indicator (Reactive Alpine) -->
                        <div>
                            <template x-if="providers['{{ $slug }}']?.status === 'connected'">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>{{ __('ai.providers.connected') }}</span>
                                    <span x-show="providers['{{ $slug }}']?.latency" class="text-[9px] opacity-80 font-mono" x-text="providers['{{ $slug }}']?.latency + 'ms'"></span>
                                </span>
                            </template>
                            <template x-if="providers['{{ $slug }}']?.status === 'error'">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    <span>{{ __('ai.providers.needs_config') }}</span>
                                </span>
                            </template>
                            <template x-if="providers['{{ $slug }}']?.status === 'untested' || !providers['{{ $slug }}']?.status">
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                    {{ $isConfigured ? __('ai.providers.saved_untested') : __('ai.providers.not_configured') }}
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Link Langsung Website Resmi Provider -->
                    @if($portal)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200/80 dark:border-white/5 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2 text-slate-700 dark:text-zinc-300 min-w-0">
                                <i data-lucide="external-link" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                <div class="truncate">
                                    <div class="font-semibold text-slate-900 dark:text-white truncate">{{ $portal['name'] }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">{{ $portal['desc'] }}</div>
                                </div>
                            </div>
                            <a href="{{ $portal['url'] }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-[11px] shrink-0 transition flex items-center gap-1 shadow-sm active:scale-95">
                                <span>Dapatkan API Key</span>
                                <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    @endif

                    <!-- Last Tested Notice & Error Banner -->
                    <div x-show="providers['{{ $slug }}']?.last_error" 
                         class="p-3 rounded-xl bg-rose-500/5 border border-rose-500/20 text-rose-700 dark:text-rose-300 text-[11px] space-y-1">
                        <div class="font-semibold flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>{{ __('ai.providers.last_diagnostic') }}</span>
                        </div>
                        <div class="leading-relaxed font-sans" x-text="providers['{{ $slug }}']?.last_error"></div>
                    </div>

                    <div x-show="providers['{{ $slug }}']?.tested_at && providers['{{ $slug }}']?.status === 'connected'" 
                         class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        <span>{{ __('ai.providers.last_verified', ['time' => '']) }}<strong x-text="providers['{{ $slug }}']?.tested_at"></strong></span>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('cooca-ai.providers.store') }}" method="POST" class="space-y-3.5" @submit="onSaveProvider($event, '{{ $slug }}')">
                        @csrf
                        <input type="hidden" name="provider" value="{{ $slug }}">

                        <!-- API Key Input with Visibility Toggle -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-semibold text-black/70 dark:text-white/70">{{ __('ai.providers.api_key') }}</label>
                                <span class="text-[10px] text-black/40 dark:text-white/40">{{ __('ai.providers.encrypted_notice') }}</span>
                            </div>
                            <div class="relative">
                                <input :type="showKey['{{ $slug }}'] ? 'text' : 'password'" 
                                       name="api_key" 
                                       id="key-{{ $slug }}"
                                       placeholder="{{ $isConfigured ? __('ai.providers.key_placeholder_saved') : __('ai.providers.key_placeholder_new') }}"
                                       class="w-full bg-black/[0.02] dark:bg-white/[0.02] border border-black/15 dark:border-white/15 rounded-xl pl-3 pr-10 py-2 text-xs font-mono text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:border-black dark:focus:border-white transition">
                                <button type="button" 
                                        @click="showKey['{{ $slug }}'] = !showKey['{{ $slug }}']"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white transition p-1">
                                    <template x-if="!showKey['{{ $slug }}']">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </template>
                                    <template x-if="showKey['{{ $slug }}']">
                                        <i data-lucide="eye-off" class="w-3.5 h-3.5"></i>
                                    </template>
                                </button>
                            </div>
                        </div>

                        <!-- Model Selection with Dynamic Detection -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-semibold text-black/70 dark:text-white/70">{{ __('ai.providers.default_model') }}</label>
                                <button type="button" 
                                        @click="detectModels('{{ $slug }}')" 
                                        :disabled="detectingModels === '{{ $slug }}'"
                                        class="text-[10px] text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 transition">
                                    <i data-lucide="refresh-cw" class="w-2.5 h-2.5" :class="detectingModels === '{{ $slug }}' ? 'animate-spin' : ''"></i>
                                    <span x-text="detectingModels === '{{ $slug }}' ? '{{ __('ai.providers.detecting') }}' : '{{ __('ai.providers.detect_models') }}'"></span>
                                </button>
                            </div>
                            <select name="model" 
                                    id="model-{{ $slug }}"
                                    class="w-full bg-black/[0.02] dark:bg-white/[0.02] border border-black/15 dark:border-white/15 rounded-xl px-3 py-2 text-xs text-black dark:text-white focus:outline-none focus:border-black dark:focus:border-white transition">
                                <template x-for="m in (providers['{{ $slug }}']?.models || [])" :key="m.id">
                                    <option :value="m.id" 
                                            :selected="m.id === '{{ $config?->model ?? '' }}'"
                                            x-text="m.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Options: Active & Default -->
                        <div class="flex items-center gap-4 pt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-black/80 dark:text-white/80">
                                <input type="checkbox" name="is_active" value="1" {{ ($config?->is_active ?? true) ? 'checked' : '' }} class="rounded border-black/20 text-black focus:ring-0">
                                <span>{{ __('ai.providers.enable_provider') }}</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer text-xs text-black/80 dark:text-white/80">
                                <input type="checkbox" name="is_default" value="1" {{ ($config?->is_default ?? false) ? 'checked' : '' }} class="rounded border-black/20 text-black focus:ring-0">
                                <span>{{ __('ai.providers.set_default') }}</span>
                            </label>
                        </div>

                        <!-- Actions Strip: Test Connection & Save -->
                        <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-2">
                            <button type="button" 
                                    @click="testProviderConnection('{{ $slug }}')"
                                    :disabled="testingProvider === '{{ $slug }}'"
                                    class="px-3 py-2 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-semibold text-black dark:text-white transition flex items-center gap-1.5 active:scale-95">
                                <i data-lucide="radio" class="w-3.5 h-3.5 text-blue-500" :class="testingProvider === '{{ $slug }}' ? 'animate-pulse text-amber-500' : ''"></i>
                                <span x-text="testingProvider === '{{ $slug }}' ? '{{ __('ai.providers.testing') }}' : '{{ __('ai.providers.test_connection') }}'"></span>
                            </button>

                            <button type="submit" 
                                    class="px-4 py-2 rounded-xl bg-black hover:bg-black/90 dark:bg-white dark:hover:bg-white/90 text-white dark:text-black text-xs font-semibold shadow-sm transition active:scale-95">
                                {{ __('ai.providers.save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        @endforeach
    </div>

</div>

<script>
function aiProvidersApp() {
    return {
        testingProvider: null,
        detectingModels: null,
        showKey: {},
        providers: {},
        toast: {
            show: false,
            message: '',
            type: 'success',
            timeout: null
        },

        initProvider(slug, status, lastError, testedAt, models) {
            this.providers[slug] = {
                status: status || 'untested',
                last_error: lastError || null,
                tested_at: testedAt || null,
                latency: null,
                models: models || []
            };
            this.showKey[slug] = false;
        },

        showToast(message, type = 'success') {
            if (this.toast.timeout) clearTimeout(this.toast.timeout);
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
            this.toast.timeout = setTimeout(() => {
                this.toast.show = false;
            }, 4500);
        },

        async detectModels(slug) {
            this.detectingModels = slug;
            const keyInput = document.getElementById('key-' + slug);
            const apiKey = keyInput ? keyInput.value : '';

            try {
                const res = await fetch("{{ route('cooca-ai.providers.detect-models') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        provider: slug,
                        api_key: apiKey
                    })
                });

                const data = await res.json();
                if (data.success && Array.isArray(data.models) && data.models.length > 0) {
                    this.providers[slug].models = data.models;
                    this.showToast(data.message || 'Model aktif berhasil dideteksi.', 'success');
                } else {
                    this.showToast(data.message || 'Gagal mendeteksi model.', 'error');
                }
            } catch (err) {
                this.showToast('Gagal terhubung: ' + err.message, 'error');
            } finally {
                this.detectingModels = null;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        async testProviderConnection(slug) {
            this.testingProvider = slug;
            const keyInput = document.getElementById('key-' + slug);
            const modelSelect = document.getElementById('model-' + slug);

            const apiKey = keyInput ? keyInput.value : '';
            const model = modelSelect ? modelSelect.value : '';

            try {
                const res = await fetch("{{ route('cooca-ai.providers.test') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        provider: slug,
                        api_key: apiKey,
                        model: model
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.providers[slug].status = 'connected';
                    this.providers[slug].last_error = null;
                    this.providers[slug].latency = data.latency_ms || null;
                    this.providers[slug].tested_at = data.tested_at_human || 'Baru saja';

                    if (Array.isArray(data.available_models) && data.available_models.length > 0) {
                        this.providers[slug].models = data.available_models;
                    }

                    if (data.model && modelSelect && modelSelect.value !== data.model) {
                        modelSelect.value = data.model;
                    }

                    this.showToast(data.message || 'Koneksi provider berhasil diverifikasi!', 'success');
                } else {
                    this.providers[slug].status = 'error';
                    this.providers[slug].last_error = data.message || 'Koneksi gagal.';
                    this.showToast(data.message || 'Koneksi gagal diverifikasi.', 'error');
                }
            } catch (err) {
                this.providers[slug].status = 'error';
                this.providers[slug].last_error = err.message;
                this.showToast('Kesalahan jaringan: ' + err.message, 'error');
            } finally {
                this.testingProvider = null;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        onSaveProvider(e, slug) {
            // Standard form post will submit and redirect with session flash
        }
    };
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) window.lucide.createIcons();
});
</script>
@endsection
