@extends('layouts.app', [
    'title' => 'Aturan Otorisasi Dokumen - Cooca',
    'headerTitle' => 'Aturan Plafon Otorisasi Dokumen',
    'headerSubtitle' => 'Konfigurasi ambang batas nominal dan tingkatan persetujuan transaksi bisnis.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{ showCreateModal: false }">

    <!-- Header Bar -->
    <header class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] flex items-center justify-center">
                <i data-lucide="sliders" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-black dark:text-white tracking-tight">
                    Aturan Plafon Otorisasi Dokumen
                </h1>
                <p class="text-xs text-black/55 dark:text-white/55">
                    Tentukan batas nominal transaksi yang mewajibkan persetujuan Supervisor, Manajer, atau Direktur.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('approvals.inbox') }}" class="min-h-[40px] px-4 rounded-[12px] text-xs font-semibold text-black/75 dark:text-white/75 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] active:scale-95 transition-all flex items-center gap-1.5">
                <i data-lucide="inbox" class="w-4 h-4"></i>
                <span>Ke Kotak Masuk</span>
            </a>
            <button
                type="button"
                @click="showCreateModal = true"
                class="min-h-[40px] px-4 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-95 transition-all shadow-md shadow-[#007AFF]/25 flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Aturan Baru</span>
            </button>
        </div>
    </header>

    <!-- Rules List Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($rules as $rule)
            <div class="glass-card bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] rounded-[22px] p-5 shadow-sm space-y-4 relative flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-[#007AFF]/10 text-[#007AFF] uppercase tracking-wider">
                            {{ match($rule->document_type) {
                                'purchase_order' => 'Purchase Order',
                                'expense' => 'Biaya Kas',
                                'supplier_invoice' => 'Faktur Supplier',
                                default => $rule->document_type
                            } }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ $rule->is_active ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-black/10 text-black/50' }}">
                            {{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ $rule->name }}</h3>
                        <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">
                            Nominal: <strong>Rp {{ number_format($rule->min_amount, 0, ',', '.') }}</strong>
                            @if($rule->max_amount)
                                s/d <strong>Rp {{ number_format($rule->max_amount, 0, ',', '.') }}</strong>
                            @else
                                <span class="text-black/40 dark:text-white/40">(Tanpa batas atas)</span>
                            @endif
                        </p>
                    </div>

                    <!-- Steps info -->
                    <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] space-y-1.5 text-xs">
                        <div class="font-semibold text-black/70 dark:text-white/70">Tingkat Persetujuan: {{ $rule->required_levels }} Level</div>
                        <ul class="space-y-1 text-black/60 dark:text-white/60 pl-1">
                            <li class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-[10px] font-bold flex items-center justify-center">1</span>
                                <span>Level 1: <strong class="capitalize">{{ $rule->approver_role_level_1 }}</strong></span>
                            </li>
                            @if ($rule->required_levels >= 2)
                            <li class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-[10px] font-bold flex items-center justify-center">2</span>
                                <span>Level 2: <strong class="capitalize">{{ $rule->approver_role_level_2 ?? '-' }}</strong></span>
                            </li>
                            @endif
                            @if ($rule->required_levels >= 3)
                            <li class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-[10px] font-bold flex items-center justify-center">3</span>
                                <span>Level 3: <strong class="capitalize">{{ $rule->approver_role_level_3 ?? '-' }}</strong></span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Footer Delete -->
                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end">
                    <form method="POST" action="{{ route('approval-rules.destroy', $rule->id) }}" onsubmit="return confirm('Hapus aturan otorisasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-[#FF3B30] hover:underline font-semibold flex items-center gap-1">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Hapus Aturan</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-3">
                <div class="w-14 h-14 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                    <i data-lucide="sliders" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-black dark:text-white">Belum Ada Aturan Otorisasi</h3>
                <p class="text-xs text-black/50 dark:text-white/50 max-w-md mx-auto">
                    Tambahkan aturan plafon nominal untuk menerapkan tata kelola otorisasi bertingkat sebelum dokumen pengadaan atau biaya dapat dicairkan.
                </p>
                <div class="pt-2">
                    <button
                        type="button"
                        @click="showCreateModal = true"
                        class="px-5 py-2.5 rounded-[14px] bg-[#007AFF] text-white text-xs font-semibold shadow-md inline-flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Buat Aturan Pertama</span>
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal Buat Aturan Baru -->
    <div
        x-show="showCreateModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm animate-fade-in"
        @keydown.escape.window="showCreateModal = false">
        <div
            @click.outside="showCreateModal = false"
            class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="sliders" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Tambah Aturan Otorisasi Baru</h3>
                    <p class="text-xs text-black/55 dark:text-white/55">Tetapkan dokumen, batas nominal, dan pihak penyetuju</p>
                </div>
            </div>

            <form method="POST" action="{{ route('approval-rules.store') }}" class="space-y-4" x-data="{ levels: 1 }">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Jenis Dokumen <span class="text-[#FF3B30]">*</span></label>
                    <select name="document_type" required class="w-full px-3.5 py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white outline-none">
                        <option value="purchase_order">Purchase Order (Pengadaan)</option>
                        <option value="expense">Pengeluaran Kas / Biaya Operasional</option>
                        <option value="supplier_invoice">Faktur Tagihan Supplier</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama Aturan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Pengeluaran Besar > 20 Juta" class="w-full px-3.5 py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nominal Minimal (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" step="1000" name="min_amount" required placeholder="10000000" class="w-full px-3.5 py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nominal Maksimal (Rp)</label>
                        <input type="number" step="1000" name="max_amount" placeholder="Kosongkan jika tanpa batas" class="w-full px-3.5 py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Jumlah Tingkat Otorisasi (Level) <span class="text-[#FF3B30]">*</span></label>
                    <select name="required_levels" x-model.number="levels" required class="w-full px-3.5 py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white outline-none">
                        <option value="1">1 Tingkat (Level 1 saja)</option>
                        <option value="2">2 Tingkat (Level 1 &amp; Level 2)</option>
                        <option value="3">3 Tingkat (Level 1, Level 2, &amp; Level 3)</option>
                    </select>
                </div>

                <div class="space-y-2.5 p-3 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Peran Penyetuju Level 1 <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="approver_role_level_1" value="supervisor" required placeholder="supervisor atau manager" class="w-full px-3 py-2 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-xs outline-none">
                    </div>

                    <div x-show="levels >= 2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Peran Penyetuju Level 2 <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="approver_role_level_2" value="manager" :required="levels >= 2" placeholder="manager" class="w-full px-3 py-2 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-xs outline-none">
                    </div>

                    <div x-show="levels >= 3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Peran Penyetuju Level 3 <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="approver_role_level_3" value="owner" :required="levels >= 3" placeholder="owner atau director" class="w-full px-3 py-2 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-xs outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="showCreateModal = false"
                        class="min-h-[40px] px-4 py-2 rounded-[14px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5">
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="min-h-[40px] px-5 py-2 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold shadow-md">
                        Simpan Aturan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
