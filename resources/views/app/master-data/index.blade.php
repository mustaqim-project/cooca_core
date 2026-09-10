@extends('layouts.app', [
    'title' => $title,
    'headerTitle' => $title,
    'headerSubtitle' => $subtitle,
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAddModal: false,
    showEditModal: false,
    activeTab: 'units',
    editUnit: { id: '', code: '', name: '', category: 'quantity' },
    deleteModalOpen: false,
    deleteTarget: { url: '', name: '' },
    openDelete(url, name) {
        this.deleteTarget = { url, name };
        this.deleteModalOpen = true;
    },
    closeDelete() {
        this.deleteModalOpen = false;
        this.deleteTarget = { url: '', name: '' };
    },
    submitDelete() {
        if (this.deleteTarget.url) {
            document.getElementById('form-delete-master').action = this.deleteTarget.url;
            document.getElementById('form-delete-master').submit();
        }
    },
    openEditUnit(unit) {
        this.editUnit = { ...unit };
        this.showEditModal = true;
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Pengaturan</span>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Master Data</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">{{ $title }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                </div>
                <div>
                    <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">{{ $title }}</h1>
                    <p class="text-[13px] text-black/50 dark:text-white/50">{{ $subtitle }}</p>
                </div>
            </div>
        </div>

        @if(in_array($type, ['material-categories', 'product-categories', 'units'], true) && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
            <button type="button" @click="showAddModal = true"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Tambah {{ $type === 'units' ? 'Satuan' : 'Data' }}</span>
            </button>
        @endif
    </header>

    <!-- ===================================================== -->
    <!-- 2. SEGMENTED CONTROL (Apple Tab Bar for Units)        -->
    <!-- ===================================================== -->
    @if($type === 'units')
        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
            <button type="button" @click="activeTab = 'units'"
                    :class="activeTab === 'units' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                    class="px-4 py-1.5 rounded-[7px] transition-all flex items-center gap-1.5">
                <span>Daftar Satuan</span>
                <span class="text-[11px] font-semibold opacity-70 tabular-nums">({{ $items->count() }})</span>
            </button>
            <button type="button" @click="activeTab = 'conversions'"
                    :class="activeTab === 'conversions' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                    class="px-4 py-1.5 rounded-[7px] transition-all flex items-center gap-1.5">
                <span>Konversi Satuan</span>
                <span class="text-[11px] font-semibold opacity-70 tabular-nums">({{ $unitConversions->count() }})</span>
            </button>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 3. MAIN DATA TABLE (Daftar Satuan / Kategori)         -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'units'" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">{{ $type === 'units' ? 'Kode Satuan' : 'Nama' }}</th>
                        @if($type === 'units')
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Nama Lengkap Satuan</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Kategori Dimensi</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Sumber</th>
                        @else
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Deskripsi</th>
                        @endif
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($items as $item)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            @if($type === 'units')
                                <td class="py-3 px-4 font-semibold text-black dark:text-white tabular-nums">{{ $item->code }}</td>
                                <td class="py-3 px-4 text-black/80 dark:text-white/80">{{ $item->name }}</td>
                                <td class="py-3 px-4 text-black/60 dark:text-white/60 capitalize">{{ $item->category }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $item->business_id ? 'bg-[#007AFF]/12 text-[#007AFF]' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60' }}">
                                        {{ $item->business_id ? 'Bisnis Ini' : 'Sistem Bawaan' }}
                                    </span>
                                </td>
                            @else
                                <td class="py-3 px-4 font-semibold text-black dark:text-white">{{ $item->name }}</td>
                                <td class="py-3 px-4 text-black/60 dark:text-white/60">{{ $item->description ?: '-' }}</td>
                            @endif
                            <td class="py-3 px-4 text-right">
                                @if($item->business_id && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
                                    <div class="flex items-center justify-end gap-1">
                                        @if($type === 'units')
                                            <button type="button" title="Edit satuan" @click="openEditUnit(@js(['id' => $item->id, 'code' => $item->code, 'name' => $item->name, 'category' => $item->category]))"
                                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                                                Edit
                                            </button>
                                        @endif
                                        <button type="button" title="Hapus" @click="openDelete('{{ route($type . '.destroy', $item->id) }}', '{{ addslashes($item->name ?? $item->code) }}')"
                                                class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors">
                                            Hapus
                                        </button>
                                    </div>
                                @else
                                    <span class="text-black/30 dark:text-white/30 text-[12px]">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-black/40 dark:text-white/40">{{ $emptyLabel }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. TAB KONVERSI SATUAN                                -->
    <!-- ===================================================== -->
    @if($type === 'units')
        <div x-show="activeTab === 'conversions'" class="space-y-5" style="display: none">
            @if(\App\Support\Context::hasPermission('master_data.units.manage'))
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <div>
                        <h2 class="text-[15px] font-semibold text-black dark:text-white">Tambah Konversi Satuan</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Tentukan perbandingan nilai konversi antar satuan (mis. 1 sak = 25 kg)</p>
                    </div>
                    <form method="POST" action="{{ route('unit-conversions.store') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        @csrf
                        <select name="from_unit_id" required class="h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="">Satuan Asal</option>
                            @foreach($items as $unit)<option value="{{ $unit->id }}">{{ $unit->code }} — {{ $unit->name }}</option>@endforeach
                        </select>
                        <select name="to_unit_id" required class="h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="">Satuan Tujuan</option>
                            @foreach($items as $unit)<option value="{{ $unit->id }}">{{ $unit->code }} — {{ $unit->name }}</option>@endforeach
                        </select>
                        <input type="number" name="factor" required min="0.000001" step="0.000001" placeholder="Faktor nilai (misal: 1000)"
                               class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <button type="submit" class="h-10 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <span>Simpan Konversi</span>
                        </button>
                    </form>
                </div>
            @endif

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Satuan Asal</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Satuan Tujuan</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Faktor Pengali</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse($unitConversions as $conversion)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="py-3 px-4 font-semibold text-black dark:text-white">{{ $conversion->fromUnit?->code }} <span class="text-[12px] font-normal text-black/50 dark:text-white/50">({{ $conversion->fromUnit?->name }})</span></td>
                                    <td class="py-3 px-4 font-semibold text-black dark:text-white">{{ $conversion->toUnit?->code }} <span class="text-[12px] font-normal text-black/50 dark:text-white/50">({{ $conversion->toUnit?->name }})</span></td>
                                    <td class="py-3 px-4 tabular-nums font-semibold text-[#007AFF]">{{ number_format((float) $conversion->factor, 6, '.', '') }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <button type="button" @click="openDelete('{{ route('unit-conversions.destroy', $conversion->id) }}', 'Konversi {{ $conversion->fromUnit?->code }} ke {{ $conversion->toUnit?->code }}')"
                                                class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-12 text-center text-black/40 dark:text-white/40">Belum ada konversi satuan bisnis terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 5. MODAL: TAMBAH DATA (Apple Sheet)                   -->
    <!-- ===================================================== -->
    @if(in_array($type, ['material-categories', 'product-categories', 'units'], true) && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
        <div x-show="showAddModal" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
            <div @click.outside="showAddModal = false" class="w-full max-w-lg rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
                <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">Tambah {{ $title }}</h3>
                    <button type="button" @click="showAddModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">✕</button>
                </div>
                <form method="POST" action="{{ route($type . '.store') }}" class="space-y-3.5 text-[13px]">
                    @csrf
                    @if($type === 'units')
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kode Satuan <span class="text-[#FF3B30]">*</span></label>
                            <input name="code" required maxlength="30" placeholder="Kode satuan, contoh: sak" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Lengkap Satuan <span class="text-[#FF3B30]">*</span></label>
                            <input name="name" required maxlength="100" placeholder="Nama lengkap satuan" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kategori Satuan <span class="text-[#FF3B30]">*</span></label>
                            <select name="category" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="quantity">Kuantitas</option><option value="weight">Berat</option><option value="volume">Volume</option><option value="length">Panjang</option><option value="time">Waktu</option><option value="area">Luas</option><option value="custom">Kustom</option>
                            </select>
                        </div>
                    @else
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama <span class="text-[#FF3B30]">*</span></label>
                            <input name="name" required maxlength="150" placeholder="{{ $nameLabel }}" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Deskripsi (Opsional)</label>
                            <textarea name="description" maxlength="500" rows="3" placeholder="Deskripsi ringkas..." class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                        </div>
                    @endif
                    <div class="flex justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                        <button type="button" @click="showAddModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] transition">Batal</button>
                        <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 6. MODAL: EDIT SATUAN (Apple Sheet)                   -->
    <!-- ===================================================== -->
    @if($type === 'units' && \App\Support\Context::hasPermission('master_data.units.manage'))
        <div x-show="showEditModal" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
            <div @click.outside="showEditModal = false" class="w-full max-w-lg rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
                <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">Edit Satuan Bisnis</h3>
                    <button type="button" @click="showEditModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">✕</button>
                </div>
                <form :action="'/units/' + editUnit.id" method="POST" class="space-y-3.5 text-[13px]">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kode Satuan <span class="text-[#FF3B30]">*</span></label>
                        <input name="code" x-model="editUnit.code" required maxlength="30" placeholder="Kode satuan" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Lengkap Satuan <span class="text-[#FF3B30]">*</span></label>
                        <input name="name" x-model="editUnit.name" required maxlength="100" placeholder="Nama lengkap satuan" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kategori Satuan <span class="text-[#FF3B30]">*</span></label>
                        <select name="category" x-model="editUnit.category" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="quantity">Kuantitas</option><option value="weight">Berat</option><option value="volume">Volume</option><option value="length">Panjang</option><option value="time">Waktu</option><option value="area">Luas</option><option value="custom">Kustom</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                        <button type="button" @click="showEditModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] transition">Batal</button>
                        <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 7. APPLE ALERT DIALOG (Hapus Data Master)             -->
    <!-- ===================================================== -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]">
        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeDelete()">
            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Data Master?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus dari sistem. Tindakan ini tidak dapat dipulihkan.
                </p>
            </div>
            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDelete()" class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDelete()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <form id="form-delete-master" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection
