@extends('layouts.app', ['title' => 'Mesin EDC & Terminal Pembayaran'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="edcTerminalApp()">

    {{-- TOOLBAR / PAGE HEADER --}}
    <x-module-header
        title="Mesin EDC & Terminal Pembayaran"
        subtitle="Kelola profil terminal EDC (TID/MID) & tarif MDR per cabang untuk transaksi kartu di kasir POS">
        <button type="button" @click="openAddModal()"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Mesin EDC</span>
        </button>
        <a href="{{ route('finance.cash-bank.index') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Kas & Rekening</span>
        </a>
    </x-module-header>

    {{-- MODULE TABS (SSOT) --}}
    <x-module-tabs module="finance" />

    {{-- STATS & FILTER BENTO BAR --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-black/50 dark:text-white/50">Total Mesin EDC</p>
                <p class="text-lg font-bold text-black dark:text-white tabular-nums">{{ $terminals->count() }} Unit</p>
            </div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-black/50 dark:text-white/50">Terminal Aktif POS</p>
                <p class="text-lg font-bold text-[#34C759] tabular-nums">{{ $terminals->where('is_active', true)->count() }} Unit</p>
            </div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-black/50 dark:text-white/50">Filter Cabang</p>
                    <form method="GET" action="{{ route('finance.edc-terminals.index') }}" id="branchFilterForm">
                        <select name="location_id" onchange="document.getElementById('branchFilterForm').submit()"
                            class="text-xs font-bold text-black dark:text-white bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                            <option value="">Semua Cabang</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            <i data-lucide="chevron-down" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
        </div>
    </div>

    {{-- TERMINALS GRID BENTO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($terminals as $term)
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border {{ $term->is_active ? 'border-black/10 dark:border-white/10' : 'border-black/5 dark:border-white/5 opacity-60' }} p-5 shadow-sm flex flex-col justify-between space-y-4 transition-all">
                <div class="space-y-3">
                    {{-- Header Card --}}
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 flex items-center justify-center font-bold text-xs text-black dark:text-white font-mono shrink-0">
                                {{ strtoupper(substr($term->bank_name, 0, 4)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-black dark:text-white leading-tight">{{ $term->terminal_name }}</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ $term->bank_name }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $term->is_active ? 'bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                            {{ $term->is_active ? 'Aktif di POS' : 'Nonaktif' }}
                        </span>
                    </div>

                    {{-- Terminal Identifiers Bento Box --}}
                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-black/50 dark:text-white/50">Terminal ID (TID):</span>
                            <span class="font-mono font-bold text-black dark:text-white tabular-nums">{{ $term->terminal_id_tid }}</span>
                        </div>
                        @if($term->merchant_id_mid)
                            <div class="flex items-center justify-between text-xs border-t border-black/5 dark:border-white/5 pt-1.5">
                                <span class="text-black/50 dark:text-white/50">Merchant ID (MID):</span>
                                <span class="font-mono text-black/80 dark:text-white/80 text-[11px] tabular-nums">{{ $term->merchant_id_mid }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between text-xs border-t border-black/5 dark:border-white/5 pt-1.5">
                            <span class="text-black/50 dark:text-white/50">Cabang Penempatan:</span>
                            <span class="font-medium text-[#007AFF] text-[11px]">{{ $term->location ? $term->location->name : 'Semua Cabang (Pusat)' }}</span>
                        </div>
                    </div>

                    {{-- MDR Rates & Settlement Target --}}
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="p-2 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <span class="text-black/45 dark:text-white/45 block">MDR Debit:</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">{{ number_format((float) $term->mdr_debit_percent, 2) }}%</span>
                        </div>
                        <div class="p-2 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <span class="text-black/45 dark:text-white/45 block">MDR Kredit:</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">{{ number_format((float) $term->mdr_credit_percent, 2) }}%</span>
                        </div>
                    </div>

                    @if($term->settlement_account_info)
                        <div class="text-[11px] text-black/60 dark:text-white/60 bg-black/[0.02] dark:bg-white/[0.03] p-2 rounded-[8px] border border-black/5 dark:border-white/5">
                            <span class="text-black/40 dark:text-white/40 block text-[10px]">Rekening Penampung Settlement:</span>
                            <span class="font-medium truncate block">{{ $term->settlement_account_info }}</span>
                        </div>
                    @endif
                </div>

                {{-- Action Bar --}}
                <div class="flex items-center justify-between pt-2 border-t border-black/5 dark:border-white/10 text-xs">
                    <form action="{{ route('finance.edc-terminals.toggle', $term->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="h-8 px-2.5 rounded-[8px] text-[11px] font-medium {{ $term->is_active ? 'text-[#FF9500] hover:bg-[#FF9500]/10' : 'text-[#34C759] hover:bg-[#34C759]/10' }} transition">
                            {{ $term->is_active ? 'Nonaktifkan' : 'Aktifkan Terminal' }}
                        </button>
                    </form>

                    <form action="{{ route('finance.edc-terminals.destroy', $term->id) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus profil mesin EDC ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center transition">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-8 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-base text-black dark:text-white">Belum Ada Mesin EDC Terdaftar</h3>
                <p class="text-xs text-black/50 dark:text-white/50 max-w-md mx-auto">
                    Daftarkan mesin EDC fisik dari bank (BCA, Mandiri, BRI, dll) yang Anda miliki di kasir toko agar kasir dapat memilih mesin EDC dan mencatat transaksi kartu secara akurat pada POS.
                </p>
                <button type="button" @click="openAddModal()"
                    class="h-10 px-5 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition inline-flex items-center gap-2 shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Mesin EDC Sekarang</span>
                </button>
            </div>
        @endforelse
    </div>

    {{-- MODAL TAMBAH MESIN EDC (BENTO SHEET XXL) --}}
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
            @click.outside="showModal = false">
            <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                <div>
                    <h2 class="text-[16px] font-bold text-black dark:text-white">Daftarkan Mesin EDC Baru</h2>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Konfigurasi Terminal ID dan nama bank untuk kasir POS</p>
                </div>
                <button type="button" @click="showModal = false" class="w-8 h-8 rounded-full hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('finance.edc-terminals.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Bank Penerbit EDC *</label>
                        <select name="bank_name" required
                            class="w-full h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">-- Pilih Bank EDC --</option>
                            <option value="BCA">Bank Central Asia (BCA)</option>
                            <option value="Mandiri">Bank Mandiri</option>
                            <option value="BRI">Bank Rakyat Indonesia (BRI)</option>
                            <option value="BNI">Bank Negara Indonesia (BNI)</option>
                            <option value="CIMB Niaga">CIMB Niaga</option>
                            <option value="Bank Permata">Bank Permata</option>
                            <option value="Bank Danamon">Bank Danamon</option>
                            <option value="BSI">Bank Syariah Indonesia (BSI)</option>
                            <option value="Lainnya">Bank / Vendor Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama / Label Terminal *</label>
                        <input type="text" name="terminal_name" required placeholder="Contoh: EDC Kasir 1 BCA"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Terminal ID (TID) *</label>
                        <input type="text" name="terminal_id_tid" required placeholder="Contoh: BCA-1092834"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono font-bold text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Merchant ID (MID) (Opsional)</label>
                        <input type="text" name="merchant_id_mid" placeholder="Contoh: MID-99882211"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Penempatan Cabang</label>
                        <select name="location_id"
                            class="w-full h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">Semua Cabang (Global)</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">MDR Debit (%)</label>
                        <input type="number" step="0.01" min="0" max="10" name="mdr_debit_percent" value="0.15"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">MDR Kredit (%)</label>
                        <input type="number" step="0.01" min="0" max="10" name="mdr_credit_percent" value="1.50"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Rekening Penampung Settlement (Opsional)</label>
                    <input type="text" name="settlement_account_info" placeholder="Contoh: Rekening BCA 8820194821 a.n Toko Abadi"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-black/5 dark:border-white/10">
                    <label class="flex items-center gap-2 text-xs text-black/70 dark:text-white/70 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-black/20 text-[#007AFF] focus:ring-0">
                        <span>Aktifkan mesin EDC ini di kasir POS</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="showModal = false"
                            class="h-10 px-4 rounded-[12px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="h-10 px-5 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition shadow-sm flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Simpan Mesin EDC</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function edcTerminalApp() {
        return {
            showModal: false,
            openAddModal() {
                this.showModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        };
    }
</script>
@endsection
