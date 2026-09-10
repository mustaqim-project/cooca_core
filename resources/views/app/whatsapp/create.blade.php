@extends('layouts.app', [
    'title' => 'Buat Blast Promosi — ' . $business->name,
    'headerTitle' => 'Buat Blast Promosi WhatsApp',
    'headerSubtitle' => 'Susun pesan promosi tertarget ke kontak pelanggan dengan live smartphone preview'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="blastForm()">

    <!-- ========================================== -->
    <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
    <!-- ========================================== -->
    <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
        <span>›</span>
        <a href="{{ route('whatsapp.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">WhatsApp Gateway</a>
        <span>›</span>
        <a href="{{ route('whatsapp.broadcast.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">Blast Promosi</a>
        <span>›</span>
        <span class="text-black/80 dark:text-white/80 font-medium">Buat Kampanye Baru</span>
    </nav>

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER BAR                               -->
    <!-- ===================================================== -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="flex items-center gap-3">
            <a href="{{ route('whatsapp.broadcast.index') }}"
                class="w-9 h-9 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/70 dark:text-white/70 flex items-center justify-center transition active:scale-[0.97]"
                title="Kembali ke Daftar Blast">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-[20px] font-bold text-black dark:text-white tracking-tight">Buat Kampanye Blast Baru</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Pilih segmen pelanggan dan tulis template promosi otomatis dengan preview langsung</p>
            </div>
        </div>
    </header>

    <!-- Warning if WhatsApp is Not Connected (Apple Tinted Warning Banner) -->
    @if(!$waSession || $waSession->status !== 'connected')
        <div class="p-4 rounded-[14px] bg-[#FF9500]/12 border border-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] text-[13px] font-medium flex items-center gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-[#FF9500]"></i>
            <div>
                <strong class="font-semibold">WhatsApp Gateway Belum Terhubung!</strong>
                <span class="ml-1">Anda harus <a href="{{ route('whatsapp.index') }}" class="underline font-semibold hover:opacity-80">memindai QR code WhatsApp</a> terlebih dahulu agar sistem dapat mendistribusikan blast pesan ke pelanggan.</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A] text-[13px] space-y-1">
            <div class="font-semibold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>Terdapat kendala pada formulir:</span>
            </div>
            <ul class="list-disc list-inside pl-2 space-y-0.5 text-[12px]">
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
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-3 transition-colors">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-black/5 dark:border-white/10">
                        <div class="w-7 h-7 rounded-[7px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                        </div>
                        <h2 class="text-[14px] font-semibold text-black dark:text-white">Identitas Kampanye</h2>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70">Judul Kampanye *</label>
                        <input type="text" name="title" x-model="title" required
                            placeholder="Contoh: Promo Gajian Weekend, Diskon Menu Baru 20%"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 text-[14px] font-medium text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-colors"
                            value="{{ old('title') }}">
                    </div>
                </div>

                <!-- CARD 2: TARGET AUDIENS -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-3 transition-colors">
                    <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-[7px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            </div>
                            <h2 class="text-[14px] font-semibold text-black dark:text-white">Pilih Target Audiens Pelanggan</h2>
                        </div>
                        <span class="text-[12px] font-semibold text-[#007AFF] tabular-nums" x-text="estimatedCount.toLocaleString('id-ID') + ' Kontak Siap'"></span>
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
                                <div class="p-3 rounded-[12px] border transition-all text-center"
                                    :class="targetFilter === '{{ $key }}'
                                        ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] shadow-[0_1px_2px_rgba(0,122,255,0.15)] font-semibold'
                                        : 'border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.03] text-black/70 dark:text-white/70 hover:border-black/10 dark:hover:border-white/10'">
                                    <div class="text-[13px] font-medium">{{ $filter['label'] }}</div>
                                    <div class="text-[11px] opacity-70 mt-0.5 tabular-nums">{{ number_format($filter['count'], 0, ',', '.') }} Kontak</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- CARD 3: PESAN PROMOSI -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-3 transition-colors">
                    <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-[7px] bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                                <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                            </div>
                            <h2 class="text-[14px] font-semibold text-black dark:text-white">Konten Pesan Promosi</h2>
                        </div>
                    </div>

                    <!-- Variable insertion chips (Apple Gray Buttons) -->
                    <div class="space-y-1.5 pt-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[12px] text-black/50 dark:text-white/50 font-medium mr-1">Tag Personal:</span>
                            @foreach(['{nama}', '{poin}', '{tier}', '{bisnis}'] as $var)
                                <button type="button" @click="insertVar('{{ $var }}')"
                                    class="h-7 px-2.5 rounded-[8px] bg-black/[0.05] hover:bg-black/[0.08] dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[12px] font-mono transition active:scale-[0.97]">
                                    {{ $var }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <textarea name="message" id="msgTextarea" x-model="message" rows="6" required
                        placeholder="Halo {nama} 👋&#10;Ada promo spesial dari {{ $business->name }} untuk tier {tier}!&#10;&#10;Dapatkan diskon 20% khusus hari ini. Tunjukkan pesan ini ke kasir 🎉"
                        @input="updatePreview()"
                        class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] p-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 resize-none font-sans placeholder:text-black/35 dark:placeholder:text-white/35 leading-relaxed transition-colors">{{ old('message') }}</textarea>
                    
                    <div class="flex items-center justify-between text-[11px] text-black/40 dark:text-white/40">
                        <span>Tip: Gunakan tanda bintang *teks* untuk cetak tebal di WhatsApp</span>
                        <span x-text="message.length + ' / 2000 Karakter'" class="tabular-nums font-medium"></span>
                    </div>
                </div>

                <!-- CARD 4: BANNER GAMBAR (OPSIONAL) -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-3 transition-colors">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-black/5 dark:border-white/10">
                        <div class="w-7 h-7 rounded-[7px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                        </div>
                        <h2 class="text-[14px] font-semibold text-black dark:text-white">Banner Gambar Promosi <span class="text-black/40 dark:text-white/40 font-normal text-[12px]">(Opsional)</span></h2>
                    </div>

                    <div class="space-y-1.5">
                        <input type="url" name="media_url" x-model="mediaUrl" @input="updatePreview()"
                            placeholder="https://example.com/banner-promo.jpg"
                            class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors"
                            value="{{ old('media_url') }}">
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">Masukkan tautan gambar publik (.jpg, .png, .webp). Gambar akan dikirimkan bersamaan dengan pesan.</p>
                    </div>
                </div>

                <!-- SUBMIT ACTION (Apple Primary Button) -->
                <button type="submit" :disabled="submitting || !message.trim() || !title.trim()"
                    class="w-full h-11 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-[13px] shadow-[0_1px_2px_rgba(0,122,255,0.25)] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-all active:scale-[0.97] active:opacity-80">
                    <i data-lucide="loader-2" x-show="submitting" class="w-4 h-4 animate-spin"></i>
                    <i data-lucide="send" x-show="!submitting" class="w-4 h-4"></i>
                    <span x-text="submitting ? 'Sedang Memproses Blast...' : 'Kirim Blast ke ' + estimatedCount.toLocaleString('id-ID') + ' Pelanggan'"></span>
                </button>
            </form>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: LIVE SMARTPHONE PREVIEW (5 cols) -->
        <!-- ========================================== -->
        <div class="lg:col-span-5">
            <div class="lg:sticky lg:top-24 space-y-3">
                <div class="flex items-center justify-between text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wide px-1">
                    <span>Live WhatsApp Preview</span>
                    <span class="text-[#007AFF] lowercase font-normal">WYSIWYG</span>
                </div>

                <!-- Smartphone Mockup Frame (iOS Device Style) -->
                <div class="relative mx-auto max-w-[300px] rounded-[36px] bg-[#1C1C1E] border-[5px] border-black/80 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.3)] overflow-hidden" style="height: 520px;">
                    
                    <!-- Top Dynamic Island Notch -->
                    <div class="absolute top-2.5 left-1/2 -translate-x-1/2 w-24 h-4 bg-black rounded-full z-20"></div>

                    <!-- WA Chat Header -->
                    <div class="bg-[#075E54] px-4 pt-8 pb-3 flex items-center gap-2.5 shadow-sm text-white">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white shrink-0">
                            <i data-lucide="bot" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[12px] font-semibold text-white truncate">{{ $business->name }}</div>
                            <div class="text-[10px] text-white/75 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                                <span>online</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Message Area -->
                    <div class="bg-[#ECE5DD] dark:bg-[#0b141a] h-full overflow-y-auto px-3 pt-3 pb-24">
                        <div class="flex justify-end mb-2">
                            <div class="max-w-[88%] rounded-[14px] rounded-tr-[4px] bg-[#DCF8C6] dark:bg-[#005c4b] px-3.5 py-2.5 shadow-sm text-black dark:text-white">
                                <!-- Media Preview -->
                                <div x-show="mediaUrl" class="mb-2 rounded-[8px] overflow-hidden bg-black/10">
                                    <img :src="mediaUrl" alt="banner" class="w-full max-h-36 object-cover rounded-[8px]" onerror="this.style.display='none'">
                                </div>
                                <!-- Message Text -->
                                <p class="text-[12px] whitespace-pre-wrap break-words leading-relaxed text-black/90 dark:text-white/95"
                                    x-text="previewMessage || 'Ketik judul dan pesan di formulir untuk melihat live simulasi tampilan...'"></p>
                                <!-- Message Meta & Double Check -->
                                <div class="text-[10px] text-black/45 dark:text-white/60 text-right mt-1.5 flex items-center justify-end gap-1">
                                    <span class="tabular-nums">{{ now()->format('H:i') }}</span>
                                    <i data-lucide="check-check" class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#40C8E0]"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <p class="text-center text-[11px] text-black/50 dark:text-white/50">
                    Preview otomatis mensimulasikan data contoh pelanggan: <em>Budi Santoso</em> (Gold Tier).
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
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
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
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },

        async submitBlast() {
            if (this.submitting) return;
            let confirmed = false;
            if (window.AppAlert) {
                confirmed = await AppAlert.confirm({
                    title: 'Kirim WhatsApp Broadcast?',
                    message: `Yakin ingin mengirim pesan massal ke ${this.estimatedCount.toLocaleString('id-ID')} pelanggan? Tindakan ini akan langsung mendistribusikan pesan ke nomor pelanggan terdaftar.`,
                    type: 'info',
                    confirmText: 'Ya, Kirim Sekarang',
                    cancelText: 'Batal'
                });
            } else {
                confirmed = confirm(`Yakin ingin mengirim pesan massal ke ${this.estimatedCount} pelanggan?`);
            }
            if (!confirmed) return;
            this.submitting = true;
            document.getElementById('blastForm').submit();
        }
    };
}
</script>
@endpush
