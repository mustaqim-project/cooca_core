@extends('layouts.public_marketing', ['title' => 'Pilih Bisnis - Cooca UMKM', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8"
        x-data="{ showCreateModal: false }">
        <div class="sm:mx-auto sm:w-full sm:max-w-xl">
            <!-- Apple HIG Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-[20px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] mb-3.5 shadow-sm">
                    <i data-lucide="building-2" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Pilih Workspace
                    Bisnis</h1>
                <p class="mt-2 text-sm text-black/60 dark:text-white/60">Pilih entitas toko yang ingin Anda operasikan atau
                    buka cabang bisnis baru</p>
            </div>

            <!-- Businesses Selection List (Apple Inset Bento Grouped) -->
            <div
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/50 space-y-5 transition-all">
                @if ($businesses->isEmpty())
                    <div class="text-center py-10 px-4 text-black/60 dark:text-white/60">
                        <div
                            class="w-14 h-14 rounded-2xl bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-center mx-auto mb-3.5 text-black/40 dark:text-white/40">
                            <i data-lucide="store" class="w-7 h-7"></i>
                        </div>
                        <h3 class="text-base font-bold text-black dark:text-white">Belum Ada Bisnis Terdaftar</h3>
                        <p class="text-xs sm:text-sm mt-1 max-w-sm mx-auto text-black/55 dark:text-white/55">Anda belum
                            memiliki bisnis aktif. Buat entitas bisnis pertama Anda untuk mulai menggunakan sistem kasir &
                            pembukuan.</p>
                        <button type="button" @click="showCreateModal = true"
                            class="mt-5 min-h-[48px] px-5 py-2.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 inline-flex items-center gap-2 transition-all active:scale-[0.98]">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Daftarkan Bisnis Baru Sekarang</span>
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        <div
                            class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 px-1">
                            Bisnis & Cabang Anda ({{ $businesses->count() }})
                        </div>
                        @foreach ($businesses as $biz)
                            <form method="POST" action="{{ route('businesses.switch') }}">
                                @csrf
                                <input type="hidden" name="business_id" value="{{ $biz->id }}">
                                <button type="submit"
                                    class="w-full min-h-[58px] p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] hover:bg-[#007AFF]/5 dark:hover:bg-[#007AFF]/10 border border-black/[0.05] dark:border-white/[0.08] hover:border-[#007AFF]/30 dark:hover:border-[#0A84FF]/30 text-left flex items-center justify-between transition-all group active:scale-[0.99]">
                                    <div class="flex items-center gap-3.5">
                                        <div
                                            class="w-11 h-11 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 group-hover:bg-[#007AFF] group-hover:text-white transition-all shrink-0 shadow-sm">
                                            <i data-lucide="store" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <div
                                                class="font-bold text-black dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors text-sm sm:text-base flex items-center gap-2">
                                                <span>{{ $biz->name }}</span>
                                                @if ($biz->is_active ?? true)
                                                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                                @endif
                                            </div>
                                            <div
                                                class="text-xs text-black/50 dark:text-white/50 flex items-center gap-2 mt-0.5">
                                                <span>Mata Uang: <strong>{{ $biz->currency_code }}</strong>
                                                    ({{ $biz->currency_symbol }})</span>
                                                <span>•</span>
                                                <span class="text-[#007AFF] dark:text-[#0A84FF] font-medium">Buka Kasir &
                                                    Dashboard</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-black/30 dark:text-white/30 group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] group-hover:bg-[#007AFF]/10 transition-all shrink-0">
                                        <i data-lucide="chevron-right"
                                            class="w-5 h-5 group-hover:translate-x-0.5 transition-transform"></i>
                                    </div>
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endif

                <div
                    class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 text-xs sm:text-sm">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-black/55 dark:text-white/55 hover:text-[#FF3B30] dark:hover:text-[#FF453A] transition-colors font-medium flex items-center gap-1.5 py-1.5">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Keluar Akun</span>
                        </button>
                    </form>
                    <button type="button" @click="showCreateModal = true"
                        class="min-h-[44px] px-4 py-2 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs sm:text-sm flex items-center gap-2 shadow-md shadow-[#007AFF]/20 transition-all active:scale-[0.98]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambah Bisnis Baru</span>
                    </button>
                </div>
            </div>

            <!-- Modal Tambah Bisnis Baru (Apple Sheet Modal with Anti-Zoom 16px Inputs) -->
            <div x-show="showCreateModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md transition-opacity"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div class="max-w-lg w-full p-6 sm:p-7 rounded-[28px] space-y-5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-left shadow-2xl relative max-h-[90vh] overflow-y-auto"
                    @click.outside="showCreateModal = false" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                    <div
                        class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white">Tambah Bisnis Baru
                                </h3>
                                <p class="text-xs text-black/50 dark:text-white/50">Buka entitas usaha atau cabang toko baru
                                    Anda</p>
                            </div>
                        </div>
                        <button type="button" @click="showCreateModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.05] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('businesses.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block font-semibold text-black/80 dark:text-white/85 mb-1.5 text-xs sm:text-sm">
                                Nama Bisnis / Entitas Usaha <span class="text-[#FF3B30]">*</span>
                            </label>
                            <input type="text" name="name" required
                                placeholder="Contoh: Kedai Kopi Senja / Toko Berkah"
                                class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 transition-all text-[16px] sm:text-sm outline-none">
                        </div>

                        <div x-data="{
                            selectedTemplate: '',
                            templateList: {{ Js::from($templateSummaries ?? []) }},
                            get currentTmpl() { return this.templateList[this.selectedTemplate] || null; }
                        }" class="space-y-2">
                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm">
                                Template Industri & Tata Letak Fitur
                            </label>
                            <select name="template_code" x-model="selectedTemplate"
                                class="w-full px-4 py-3 bg-black/[0.03] dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[14px] text-black dark:text-white focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 transition-all text-[16px] sm:text-sm outline-none">
                                <option value="">-- Setup Manual (Semua Fitur Tersedia) --</option>
                                @foreach ($templates as $tmpl)
                                    <option value="{{ $tmpl->code }}">{{ $tmpl->name }}
                                        ({{ strtoupper($tmpl->industry_category) }})</option>
                                @endforeach
                            </select>

                            <template x-if="currentTmpl">
                                <div
                                    class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] space-y-2 text-xs">
                                    <div class="flex items-center justify-between font-semibold">
                                        <span class="text-[#007AFF] dark:text-[#0A84FF]"
                                            x-text="'Penataan Modul: ' + currentTmpl.name"></span>
                                        <span
                                            class="px-2.5 py-0.5 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-[10px] font-bold"
                                            x-text="currentTmpl.category"></span>
                                    </div>
                                    <div class="text-black/70 dark:text-white/70">
                                        <span class="text-[#34C759] dark:text-[#30D158] font-semibold">Modul Aktif:</span>
                                        <span x-text="currentTmpl.enabled.map(i => i.name).join(', ')"></span>
                                    </div>
                                    <template x-if="currentTmpl.disabled && currentTmpl.disabled.length > 0">
                                        <div
                                            class="text-black/45 dark:text-white/45 text-[11px] pt-1.5 border-t border-black/5 dark:border-white/5">
                                            <span>Modul Disembunyikan: </span>
                                            <span class="line-through"
                                                x-text="currentTmpl.disabled.map(i => i.name).join(', ')"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <p class="text-[11px] sm:text-xs text-black/50 dark:text-white/50">
                                Sistem secara otomatis menyesuaikan tampilan POS Kasir dan modul operasional sesuai industri
                                pilihan Anda.
                            </p>
                        </div>

                        <div>
                            <label class="block font-semibold text-black/80 dark:text-white/85 mb-1.5 text-xs sm:text-sm">
                                Mata Uang Utama Transaksi
                            </label>
                            <select name="currency"
                                class="w-full px-4 py-3 bg-black/[0.03] dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[14px] text-black dark:text-white focus:border-[#007AFF] transition-all text-[16px] sm:text-sm outline-none">
                                <option value="IDR">IDR (Rp - Rupiah Indonesia)</option>
                                <option value="USD">USD ($ - US Dollar)</option>
                                <option value="SGD">SGD (S$ - Singapore Dollar)</option>
                                <option value="MYR">MYR (RM - Malaysian Ringgit)</option>
                            </select>
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <button type="button" @click="showCreateModal = false"
                                class="min-h-[46px] px-5 py-2.5 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/75 dark:text-white/80 font-semibold text-xs sm:text-sm transition-all">
                                Batal
                            </button>
                            <button type="submit"
                                class="min-h-[46px] px-5 py-2.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs sm:text-sm flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 transition-all active:scale-[0.98]">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Simpan & Buka Bisnis</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
