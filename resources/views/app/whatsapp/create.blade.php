@extends('layouts.app')

@section('title', 'Buat Blast Promosi — ' . $business->name)

@section('content')
<div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6" x-data="blastForm()">

    {{-- PAGE HEADER --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('whatsapp.broadcast.index') }}" class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#25D366] to-[#128C7E] flex items-center justify-center shadow-lg shadow-emerald-500/20">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-white">Buat Blast Promosi</h1>
            <p class="text-sm text-slate-400">Kirim pesan promosi ke pelanggan terpilih</p>
        </div>
    </div>

    @if(!$waSession || $waSession->status !== 'connected')
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <span class="font-bold">WhatsApp belum terhubung!</span>
                <span class="ml-1">Harap <a href="{{ route('whatsapp.index') }}" class="underline hover:text-rose-300">hubungkan WA Anda</a> terlebih dahulu sebelum membuat blast.</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- LEFT: FORM --}}
        <div class="lg:col-span-3 space-y-4">
            <form action="{{ route('whatsapp.broadcast.store') }}" method="POST" id="blastForm" @submit.prevent="submitBlast">
                @csrf

                {{-- JUDUL KAMPANYE --}}
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800">
                        <h2 class="text-sm font-bold text-white">Detail Kampanye</h2>
                    </div>
                    <div class="p-5 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1.5">Judul Kampanye</label>
                            <input type="text" name="title" x-model="title" required
                                placeholder="Promo Lebaran 2026, Diskon Akhir Bulan, dll."
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#25D366]/60 placeholder:text-slate-600"
                                value="{{ old('title') }}">
                        </div>
                    </div>
                </div>

                {{-- TARGET AUDIENS --}}
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800">
                        <h2 class="text-sm font-bold text-white">Target Audiens</h2>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @php
                                $filters = [
                                    'all'    => ['label' => 'Semua Pelanggan', 'count' => $customerCount, 'color' => 'slate'],
                                    'bronze' => ['label' => 'Bronze', 'count' => $tierCounts['bronze'] ?? 0, 'color' => 'amber'],
                                    'silver' => ['label' => 'Silver', 'count' => $tierCounts['silver'] ?? 0, 'color' => 'slate'],
                                    'gold'   => ['label' => 'Gold', 'count' => $tierCounts['gold'] ?? 0, 'color' => 'yellow'],
                                    'vip'    => ['label' => 'VIP', 'count' => $tierCounts['vip'] ?? 0, 'color' => 'purple'],
                                ];
                            @endphp
                            @foreach($filters as $key => $filter)
                                <label class="cursor-pointer">
                                    <input type="radio" name="target_filter" value="{{ $key }}" x-model="targetFilter" class="sr-only"
                                        {{ old('target_filter', 'all') === $key ? 'checked' : '' }}>
                                    <div class="p-3 rounded-xl border transition text-center"
                                        :class="targetFilter === '{{ $key }}'
                                            ? 'border-[#25D366]/60 bg-[#25D366]/10 text-[#25D366]'
                                            : 'border-slate-700 bg-slate-950/50 text-slate-400 hover:border-slate-600'">
                                        <div class="font-bold text-sm">{{ $filter['label'] }}</div>
                                        <div class="text-[10px] opacity-70 mt-0.5">{{ number_format($filter['count']) }} pelanggan</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- PESAN PROMOSI --}}
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-white">Pesan Promosi</h2>
                    </div>
                    <div class="p-5 space-y-3">
                        {{-- Variable Chips --}}
                        <div class="flex flex-wrap gap-1.5">
                            <span class="text-xs text-slate-500 font-semibold mr-1 self-center">Variabel:</span>
                            @foreach(['{nama}', '{poin}', '{tier}', '{bisnis}'] as $var)
                                <button type="button" @click="insertVar('{{ $var }}')"
                                    class="px-2.5 py-0.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-mono transition border border-slate-700">
                                    {{ $var }}
                                </button>
                            @endforeach
                        </div>
                        <textarea name="message" id="msgTextarea" x-model="message" rows="6" required
                            placeholder="Halo {nama} 👋&#10;Ada promo spesial dari {{ $business->name }}!&#10;&#10;Diskon 20% untuk semua menu hari ini 🎉"
                            @input="updatePreview()"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#25D366]/60 resize-none font-mono placeholder:text-slate-600">{{ old('message') }}</textarea>
                        <div class="text-xs text-slate-500 text-right" x-text="message.length + ' / 2000 karakter'"></div>
                    </div>
                </div>

                {{-- MEDIA (OPSIONAL) --}}
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-800">
                        <h2 class="text-sm font-bold text-white">Banner Gambar <span class="text-slate-500 font-normal">(Opsional)</span></h2>
                    </div>
                    <div class="p-5">
                        <input type="url" name="media_url" x-model="mediaUrl" @input="updatePreview()"
                            placeholder="https://example.com/banner-promo.jpg"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#25D366]/60 placeholder:text-slate-600"
                            value="{{ old('media_url') }}">
                        <p class="text-xs text-slate-500 mt-2">URL gambar publik (.jpg, .png, .webp). Maks 5MB.</p>
                    </div>
                </div>

                {{-- SUBMIT --}}
                <button type="submit" :disabled="submitting || !message.trim() || !title.trim()"
                    class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-[#25D366] to-[#128C7E] hover:from-[#22c55e] hover:to-[#0f7a6a] text-white font-bold text-sm transition shadow-xl shadow-[#25D366]/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="submitting ? 'Mengirim Blast...' : '🚀 Kirim Blast ke ' + estimatedCount + ' Pelanggan'"></span>
                </button>
            </form>
        </div>

        {{-- RIGHT: LIVE SMARTPHONE PREVIEW --}}
        <div class="lg:col-span-2">
            <div class="sticky top-6">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Live Preview</div>
                {{-- Smartphone Frame --}}
                <div class="relative mx-auto" style="max-width: 280px">
                    <div class="rounded-[2.5rem] bg-slate-950 border-4 border-slate-700 shadow-2xl overflow-hidden" style="height: 500px;">
                        {{-- WA Header --}}
                        <div class="bg-[#075E54] px-4 pt-10 pb-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#25D366]/30 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white">{{ $business->name }}</div>
                                <div class="text-[10px] text-[#25D366]">online</div>
                            </div>
                        </div>
                        {{-- Chat Area --}}
                        <div class="bg-[#E5DDD5] h-full overflow-y-auto px-3 pt-3 pb-12" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'260\' height=\'260\'%3E%3C/svg%3E')">
                            <div class="flex justify-end mb-2">
                                <div class="max-w-[85%] rounded-2xl rounded-tr-sm bg-[#DCF8C6] px-3 py-2 shadow-sm">
                                    {{-- Media preview --}}
                                    <div x-show="mediaUrl" class="mb-2 rounded-xl overflow-hidden">
                                        <img :src="mediaUrl" alt="banner" class="w-full max-h-32 object-cover rounded-xl" onerror="this.style.display='none'">
                                    </div>
                                    {{-- Message text --}}
                                    <p class="text-[11px] text-[#303030] whitespace-pre-wrap break-words"
                                        x-text="previewMessage || 'Ketik pesan di form untuk melihat preview...'"></p>
                                    <div class="text-[9px] text-[#8B8B8B] text-right mt-1 flex items-center justify-end gap-1">
                                        <span>{{ now()->format('H:i') }}</span>
                                        <svg class="w-3 h-3 text-[#4FC3F7]" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center text-xs text-slate-600 mt-3">Preview menggunakan contoh data pelanggan</div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function blastForm() {
    return {
        title: '{{ old('title') }}',
        targetFilter: '{{ old('target_filter', 'all') }}',
        message: `{{ old('message') }}`,
        mediaUrl: '{{ old('media_url') }}',
        previewMessage: '',
        submitting: false,
        estimatedCount: {{ $customerCount }},
        tierCounts: @json($tierCounts + ['all' => $customerCount]),

        init() {
            this.updatePreview();
            this.$watch('targetFilter', (val) => {
                this.estimatedCount = this.tierCounts[val] ?? 0;
            });
        },

        insertVar(v) {
            const ta = document.getElementById('msgTextarea');
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            this.message = this.message.substring(0, start) + v + this.message.substring(end);
            this.$nextTick(() => {
                ta.selectionStart = ta.selectionEnd = start + v.length;
                ta.focus();
                this.updatePreview();
            });
        },

        updatePreview() {
            this.previewMessage = this.message
                .replace(/\{nama\}/g, 'Budi Santoso')
                .replace(/\{poin\}/g, '1.250')
                .replace(/\{tier\}/g, 'Gold')
                .replace(/\{bisnis\}/g, '{{ addslashes($business->name) }}');
        },

        async submitBlast() {
            if (this.submitting) return;
            if (!confirm(`Yakin ingin mengirim blast ke ${this.estimatedCount} pelanggan? Proses ini tidak dapat dibatalkan.`)) return;
            this.submitting = true;
            document.getElementById('blastForm').submit();
        }
    };
}
</script>
@endpush
