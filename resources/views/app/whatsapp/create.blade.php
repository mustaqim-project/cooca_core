@extends('layouts.app', [
    'title' => 'Buat Blast Promosi — ' . $business->name,
    'headerTitle' => 'Buat Blast Promosi WhatsApp',
    'headerSubtitle' => 'Susun pesan promosi tertarget ke kontak pelanggan dengan live smartphone preview'
])

@section('content')
<div class="space-y-6" x-data="blastForm()">

    <!-- Top Navigation Bar -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('whatsapp.broadcast.index') }}"
                class="p-2.5 rounded-xl bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 transition-colors shadow-xs"
                title="Kembali ke Daftar Blast">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Buat Kampanye Blast Baru</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih segmen pelanggan dan tulis template promosi otomatis</p>
            </div>
        </div>
    </div>

    <!-- Warning if WhatsApp is Not Connected -->
    @if(!$waSession || $waSession->status !== 'connected')
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-semibold flex items-center gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400"></i>
            <div>
                <span class="font-bold">WhatsApp Gateway Belum Terhubung!</span>
                <span class="ml-1">Anda harus <a href="{{ route('whatsapp.index') }}" class="underline font-bold text-rose-700 dark:text-rose-200 hover:text-rose-900">memindai QR code WhatsApp</a> terlebih dahulu agar sistem dapat mengirimkan blast ke pelanggan.</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>Terdapat kendala pada input:</span>
            </div>
            <ul class="list-disc list-inside pl-2 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- ========================================== -->
        <!-- LEFT COLUMN: CAMPAIGN COMPOSER FORM (7 cols) -->
        <!-- ========================================== -->
        <div class="lg:col-span-7 space-y-5">
            <form action="{{ route('whatsapp.broadcast.store') }}" method="POST" id="blastForm" @submit.prevent="submitBlast" class="space-y-5">
                @csrf

                <!-- CARD 1: DETAIL KAMPANYE -->
                <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-6 space-y-3 transition-colors">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Identitas Kampanye</h2>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Judul Kampanye *</label>
                        <input type="text" name="title" x-model="title" required
                            placeholder="Contoh: Promo Gajian Weekend, Diskon Menu Baru 20%"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 placeholder:text-slate-400 transition-colors"
                            value="{{ old('title') }}">
                    </div>
                </div>

                <!-- CARD 2: TARGET AUDIENS -->
                <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-6 space-y-3 transition-colors">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="p-1.5 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                <i data-lucide="users" class="w-4 h-4"></i>
                            </div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Pilih Target Audiens Pelanggan</h2>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono" x-text="estimatedCount + ' Kontak Siap'"></span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-1">
                        @php
                            $filters = [
                                'all'    => ['label' => 'Semua Pelanggan', 'count' => $customerCount],
                                'bronze' => ['label' => 'Bronze Tier', 'count' => $tierCounts['bronze'] ?? 0],
                                'silver' => ['label' => 'Silver Tier', 'count' => $tierCounts['silver'] ?? 0],
                                'gold'   => ['label' => 'Gold Tier', 'count' => $tierCounts['gold'] ?? 0],
                                'vip'    => ['label' => 'VIP Member', 'count' => $tierCounts['vip'] ?? 0],
                            ];
                        @endphp
                        @foreach($filters as $key => $filter)
                            <label class="cursor-pointer select-none">
                                <input type="radio" name="target_filter" value="{{ $key }}" x-model="targetFilter" class="sr-only"
                                    {{ old('target_filter', 'all') === $key ? 'checked' : '' }}>
                                <div class="p-3 rounded-2xl border transition-all text-center"
                                    :class="targetFilter === '{{ $key }}'
                                        ? 'border-emerald-500 bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 dark:border-emerald-500/40 shadow-xs'
                                        : 'border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/60 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <div class="font-bold text-xs">{{ $filter['label'] }}</div>
                                    <div class="text-[10px] opacity-80 mt-0.5 font-mono">{{ number_format($filter['count']) }} Kontak</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- CARD 3: PESAN PROMOSI -->
                <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-6 space-y-3 transition-colors">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="p-1.5 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400">
                                <i data-lucide="message-square" class="w-4 h-4"></i>
                            </div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Konten Pesan Promosi</h2>
                        </div>
                    </div>

                    <!-- Variable insertion chips -->
                    <div class="space-y-1.5 pt-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold mr-1">Tag Personal:</span>
                            @foreach(['{nama}', '{poin}', '{tier}', '{bisnis}'] as $var)
                                <button type="button" @click="insertVar('{{ $var }}')"
                                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-mono transition-colors border border-slate-200 dark:border-slate-700 shadow-2xs">
                                    {{ $var }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <textarea name="message" id="msgTextarea" x-model="message" rows="6" required
                        placeholder="Halo {nama} 👋&#10;Ada promo spesial dari {{ $business->name }} untuk tier {tier}!&#10;&#10;Dapatkan diskon 20% khusus hari ini. Tunjukkan pesan ini ke kasir 🎉"
                        @input="updatePreview()"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 py-3 text-xs text-slate-900 dark:text-white focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 resize-none font-mono placeholder:text-slate-400 leading-relaxed transition-colors">{{ old('message') }}</textarea>
                    
                    <div class="flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                        <span>Gunakan tanda bintang *teks* untuk cetak tebal di WhatsApp</span>
                        <span x-text="message.length + ' / 2000 Karakter'" class="font-mono"></span>
                    </div>
                </div>

                <!-- CARD 4: BANNER GAMBAR (OPSIONAL) -->
                <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-6 space-y-3 transition-colors">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="p-1.5 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400">
                            <i data-lucide="image" class="w-4 h-4"></i>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Banner Gambar Promosi <span class="text-slate-400 font-normal text-xs">(Opsional)</span></h2>
                    </div>

                    <div>
                        <input type="url" name="media_url" x-model="mediaUrl" @input="updatePreview()"
                            placeholder="https://example.com/banner-promo.jpg"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 placeholder:text-slate-400 transition-colors"
                            value="{{ old('media_url') }}">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">Masukkan tautan gambar publik (.jpg, .png, .webp). Gambar akan dikirimkan bersamaan dengan pesan WhatsApp.</p>
                    </div>
                </div>

                <!-- SUBMIT ACTION -->
                <button type="submit" :disabled="submitting || !message.trim() || !title.trim()"
                    class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-sm shadow-md shadow-emerald-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-all active:scale-98">
                    <i data-lucide="loader-2" x-show="submitting" class="w-4 h-4 animate-spin"></i>
                    <i data-lucide="send" x-show="!submitting" class="w-4 h-4"></i>
                    <span x-text="submitting ? 'Sedang Memproses Blast...' : 'Kirim Blast ke ' + estimatedCount + ' Pelanggan'"></span>
                </button>
            </form>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: LIVE SMARTPHONE PREVIEW (5 cols) -->
        <!-- ========================================== -->
        <div class="lg:col-span-5">
            <div class="lg:sticky lg:top-24 space-y-3">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-1">
                    <span>Live WhatsApp Preview</span>
                    <span class="text-emerald-600 dark:text-emerald-400 lowercase font-normal">WYSIWYG</span>
                </div>

                <!-- Smartphone Mockup Frame -->
                <div class="relative mx-auto max-w-[300px] rounded-[2.5rem] bg-slate-950 border-4 border-slate-700 dark:border-slate-800 shadow-2xl overflow-hidden" style="height: 520px;">
                    
                    <!-- WA Chat Header -->
                    <div class="bg-[#075E54] px-4 pt-10 pb-3 flex items-center gap-3 shadow-sm">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/30 flex items-center justify-center text-white shrink-0">
                            <i data-lucide="bot" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-white truncate">{{ $business->name }}</div>
                            <div class="text-[10px] text-emerald-300 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>online</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Message Area -->
                    <div class="bg-[#E5DDD5] dark:bg-[#0b141a] h-full overflow-y-auto px-3.5 pt-3.5 pb-16">
                        <div class="flex justify-end mb-2">
                            <div class="max-w-[88%] rounded-2xl rounded-tr-xs bg-[#DCF8C6] dark:bg-[#005c4b] px-3.5 py-2.5 shadow-sm text-slate-900 dark:text-white">
                                <!-- Media Preview -->
                                <div x-show="mediaUrl" class="mb-2 rounded-xl overflow-hidden bg-black/10">
                                    <img :src="mediaUrl" alt="banner" class="w-full max-h-36 object-cover rounded-xl" onerror="this.style.display='none'">
                                </div>
                                <!-- Message Text -->
                                <p class="text-xs whitespace-pre-wrap break-words leading-relaxed text-slate-900 dark:text-slate-100"
                                    x-text="previewMessage || 'Ketik judul dan pesan di formulir untuk melihat live simulasi tampilan...'"></p>
                                <!-- Message Meta & Double Check -->
                                <div class="text-[9px] text-slate-500 dark:text-emerald-200/70 text-right mt-1.5 flex items-center justify-end gap-1">
                                    <span>{{ now()->format('H:i') }}</span>
                                    <i data-lucide="check-check" class="w-3.5 h-3.5 text-blue-500 dark:text-cyan-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <p class="text-center text-[11px] text-slate-500 dark:text-slate-400">
                    Preview otomatis mensimulasikan data contoh pelanggan: <em>Budi Santoso</em>.
                </p>
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
            const confirmed = await AppAlert.confirm({
                title: 'Kirim WhatsApp Broadcast?',
                message: `Yakin ingin mengirim pesan massal ke ${this.estimatedCount} pelanggan? Tindakan ini akan langsung mendistribusikan pesan.`,
                type: 'info',
                confirmText: 'Ya, Kirim Sekarang',
                cancelText: 'Batal'
            });
            if (!confirmed) return;
            this.submitting = true;
            document.getElementById('blastForm').submit();
        }
    };
}
</script>
@endpush
