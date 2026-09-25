@extends('layouts.app', [
    'title' => 'Aturan Otorisasi Dokumen - Cooca',
    'headerTitle' => 'Aturan Plafon Otorisasi Dokumen',
    'headerSubtitle' => 'Konfigurasi ambang batas nominal dan tingkatan persetujuan transaksi bisnis.'
])

@section('content')
<div class="max-w-[1440px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="approvalRulesManager()">

    <!-- Header Bar -->
    <header class="rounded-[20px] backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <i data-lucide="sliders" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    Pengaturan Kebijakan
                </div>
                <h1 class="text-xl font-bold text-black dark:text-white tracking-tight">
                    Aturan Plafon Otorisasi Dokumen
                </h1>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('approvals.inbox') }}" class="min-h-[44px] px-4 rounded-[12px] text-xs font-semibold text-black/75 dark:text-white/75 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] active:scale-[0.98] transition-all flex items-center gap-1.5">
                <i data-lucide="inbox" class="w-4 h-4"></i>
                <span>Ke Kotak Masuk</span>
            </a>
            <button
                type="button"
                @click="openCreateModal()"
                class="min-h-[44px] px-4 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-sm flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Aturan Baru</span>
            </button>
        </div>
    </header>

    <!-- Rules List Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($rules as $rule)
            <div class="rounded-[22px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-4 relative flex flex-col justify-between hover:border-[#007AFF]/30 transition-all">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] uppercase tracking-wider">
                            {{ match($rule->document_type) {
                                'purchase_order' => 'Purchase Order',
                                'expense' => 'Biaya Kas',
                                'supplier_invoice' => 'Faktur Supplier',
                                default => $rule->document_type
                            } }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $rule->is_active ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                            {{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ $rule->name }}</h3>
                        <p class="text-xs text-black/60 dark:text-white/60 mt-0.5 tabular-nums">
                            Nominal: <strong class="text-black dark:text-white">Rp {{ number_format($rule->min_amount, 0, ',', '.') }}</strong>
                            @if($rule->max_amount)
                                s/d <strong class="text-black dark:text-white">Rp {{ number_format($rule->max_amount, 0, ',', '.') }}</strong>
                            @else
                                <span class="text-black/40 dark:text-white/40 font-normal">(Tanpa batas atas)</span>
                            @endif
                        </p>
                    </div>

                    <!-- Steps info -->
                    <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-2 text-xs">
                        <div class="font-bold text-black/70 dark:text-white/70 flex items-center justify-between">
                            <span>Tingkat Persetujuan:</span>
                            <span class="px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[10.5px] font-bold">{{ $rule->required_levels }} Tingkat</span>
                        </div>
                        <ul class="space-y-1.5 text-black/70 dark:text-white/70">
                            <li class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-[10px] font-bold flex items-center justify-center shrink-0">1</span>
                                <span>Level 1: <strong class="capitalize text-black dark:text-white">{{ $rule->approver_role_level_1 }}</strong></span>
                            </li>
                            @if ($rule->required_levels >= 2)
                            <li class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-[10px] font-bold flex items-center justify-center shrink-0">2</span>
                                <span>Level 2: <strong class="capitalize text-black dark:text-white">{{ $rule->approver_role_level_2 ?? '-' }}</strong></span>
                            </li>
                            @endif
                            @if ($rule->required_levels >= 3)
                            <li class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-[10px] font-bold flex items-center justify-center shrink-0">3</span>
                                <span>Level 3: <strong class="capitalize text-black dark:text-white">{{ $rule->approver_role_level_3 ?? '-' }}</strong></span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Footer Actions: Edit & Delete -->
                <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-2">
                    <button
                        type="button"
                        @click="openEditModal({{ Js::from($rule) }})"
                        class="min-h-[36px] px-3 rounded-[10px] text-xs text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 font-semibold transition-all active:scale-[0.98] flex items-center gap-1">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Ubah Aturan</span>
                    </button>
                    
                    <form method="POST" action="{{ route('approval-rules.destroy', $rule->id) }}" onsubmit="return confirm('Hapus aturan otorisasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="min-h-[36px] px-2 text-xs text-[#FF3B30] hover:underline font-semibold flex items-center gap-1">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Hapus</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] space-y-3">
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
                        @click="openCreateModal()"
                        class="min-h-[44px] px-5 py-2.5 rounded-[12px] bg-[#007AFF] text-white text-xs font-semibold shadow-sm inline-flex items-center gap-1.5 active:scale-[0.98]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Buat Aturan Pertama</span>
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- ======================================================= -->
    <!-- MODAL BUAT ATURAN BARU                                  -->
    <!-- ======================================================= -->
    <div
        x-show="createModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-md animate-fade-in"
        @keydown.escape.window="createModalOpen = false">
        
        <div
            @click.outside="createModalOpen = false"
            class="w-full sm:max-w-lg bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-2xl space-y-4 max-h-[94vh] flex flex-col overflow-hidden">
            
            <!-- Mobile Grab Bar -->
            <div class="w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-2 mb-2 sm:hidden shrink-0"></div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="sliders" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Tambah Aturan Otorisasi Baru</h3>
                    <p class="text-xs text-black/55 dark:text-white/55">Tetapkan dokumen, batas nominal, dan pihak penyetuju</p>
                </div>
            </div>

            <form method="POST" action="{{ route('approval-rules.store') }}" class="space-y-4 overflow-y-auto pr-1">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Jenis Dokumen <span class="text-[#FF3B30]">*</span></label>
                    <select name="document_type" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        <option value="purchase_order">Purchase Order (Pengadaan)</option>
                        <option value="expense">Pengeluaran Kas / Biaya Operasional</option>
                        <option value="supplier_invoice">Faktur Tagihan Supplier</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama Aturan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Pengeluaran Besar > 10 Juta" class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nominal Minimal (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" step="1000" name="min_amount" required placeholder="10000000" class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nominal Maksimal (Rp)</label>
                        <input type="number" step="1000" name="max_amount" placeholder="Kosongkan jika tanpa batas" class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Jumlah Tingkat Otorisasi <span class="text-[#FF3B30]">*</span></label>
                    <select name="required_levels" x-model.number="formLevels" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        <option value="1">1 Tingkat (Level 1 saja)</option>
                        <option value="2">2 Tingkat (Level 1 &amp; Level 2)</option>
                        <option value="3">3 Tingkat (Level 1, Level 2, &amp; Level 3)</option>
                    </select>
                </div>

                <div class="space-y-2.5 p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Peran Penyetuju Level 1 <span class="text-[#FF3B30]">*</span></label>
                        <select name="approver_role_level_1" required class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                            <option value="supervisor" selected>Supervisor</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner / Pemilik Toko</option>
                            @foreach($availableRoles as $r)
                                @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                    <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div x-show="formLevels >= 2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Peran Penyetuju Level 2 <span class="text-[#FF3B30]">*</span></label>
                        <select name="approver_role_level_2" :required="formLevels >= 2" class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                            <option value="manager" selected>Manager</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner / Pemilik Toko</option>
                            @foreach($availableRoles as $r)
                                @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                    <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div x-show="formLevels >= 3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Peran Penyetuju Level 3 <span class="text-[#FF3B30]">*</span></label>
                        <select name="approver_role_level_3" :required="formLevels >= 3" class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                            <option value="owner" selected>Owner / Pemilik Toko</option>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            @foreach($availableRoles as $r)
                                @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                    <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-black/75 dark:text-white/75">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                        <span>Aturan Aktif</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="createModalOpen = false"
                        class="min-h-[44px] px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 active:scale-[0.98]">
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="min-h-[44px] px-5 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold shadow-sm active:scale-[0.98]">
                        Simpan Aturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL EDIT ATURAN                                       -->
    <!-- ======================================================= -->
    <div
        x-show="editModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-md animate-fade-in"
        @keydown.escape.window="editModalOpen = false">
        
        <div
            @click.outside="editModalOpen = false"
            class="w-full sm:max-w-lg bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-2xl space-y-4 max-h-[94vh] flex flex-col overflow-hidden">
            
            <!-- Mobile Grab Bar -->
            <div class="w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-2 mb-2 sm:hidden shrink-0"></div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Ubah Aturan Otorisasi</h3>
                    <p class="text-xs text-black/55 dark:text-white/55" x-text="editForm.name"></p>
                </div>
            </div>

            <form method="POST" :action="'{{ url('/settings/approval-rules') }}/' + editForm.id" class="space-y-4 overflow-y-auto pr-1">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Jenis Dokumen <span class="text-[#FF3B30]">*</span></label>
                    <select name="document_type" x-model="editForm.document_type" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        <option value="purchase_order">Purchase Order (Pengadaan)</option>
                        <option value="expense">Pengeluaran Kas / Biaya Operasional</option>
                        <option value="supplier_invoice">Faktur Tagihan Supplier</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama Aturan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" x-model="editForm.name" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nominal Minimal (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" step="1000" name="min_amount" x-model="editForm.min_amount" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nominal Maksimal (Rp)</label>
                        <input type="number" step="1000" name="max_amount" x-model="editForm.max_amount" placeholder="Kosongkan jika tanpa batas" class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Jumlah Tingkat Otorisasi <span class="text-[#FF3B30]">*</span></label>
                    <select name="required_levels" x-model.number="editForm.required_levels" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        <option value="1">1 Tingkat (Level 1 saja)</option>
                        <option value="2">2 Tingkat (Level 1 &amp; Level 2)</option>
                        <option value="3">3 Tingkat (Level 1, Level 2, &amp; Level 3)</option>
                    </select>
                </div>

                <div class="space-y-2.5 p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Peran Penyetuju Level 1 <span class="text-[#FF3B30]">*</span></label>
                        <select name="approver_role_level_1" x-model="editForm.approver_role_level_1" required class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                            <option value="supervisor">Supervisor</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner / Pemilik Toko</option>
                            @foreach($availableRoles as $r)
                                @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                    <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div x-show="editForm.required_levels >= 2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Peran Penyetuju Level 2 <span class="text-[#FF3B30]">*</span></label>
                        <select name="approver_role_level_2" x-model="editForm.approver_role_level_2" :required="editForm.required_levels >= 2" class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                            <option value="manager">Manager</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner / Pemilik Toko</option>
                            @foreach($availableRoles as $r)
                                @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                    <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div x-show="editForm.required_levels >= 3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Peran Penyetuju Level 3 <span class="text-[#FF3B30]">*</span></label>
                        <select name="approver_role_level_3" x-model="editForm.approver_role_level_3" :required="editForm.required_levels >= 3" class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                            <option value="owner">Owner / Pemilik Toko</option>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            @foreach($availableRoles as $r)
                                @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                    <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-black/75 dark:text-white/75">
                        <input type="checkbox" name="is_active" value="1" x-model="editForm.is_active" class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                        <span>Aturan Aktif</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="editModalOpen = false"
                        class="min-h-[44px] px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 active:scale-[0.98]">
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="min-h-[44px] px-5 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold shadow-sm active:scale-[0.98]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function approvalRulesManager() {
    return {
        createModalOpen: false,
        editModalOpen: false,
        formLevels: 1,
        editForm: {
            id: '',
            name: '',
            document_type: 'purchase_order',
            min_amount: 0,
            max_amount: null,
            required_levels: 1,
            approver_role_level_1: 'supervisor',
            approver_role_level_2: 'manager',
            approver_role_level_3: 'owner',
            is_active: true
        },

        openCreateModal() {
            this.formLevels = 1;
            this.createModalOpen = true;
        },

        openEditModal(rule) {
            this.editForm = {
                id: rule.id,
                name: rule.name,
                document_type: rule.document_type,
                min_amount: rule.min_amount,
                max_amount: rule.max_amount,
                required_levels: rule.required_levels,
                approver_role_level_1: rule.approver_role_level_1 || 'supervisor',
                approver_role_level_2: rule.approver_role_level_2 || 'manager',
                approver_role_level_3: rule.approver_role_level_3 || 'owner',
                is_active: Boolean(rule.is_active)
            };
            this.editModalOpen = true;
        }
    };
}
</script>
@endsection
