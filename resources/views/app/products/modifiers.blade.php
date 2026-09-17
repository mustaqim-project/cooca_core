@extends('layouts.app', [
    'title' => 'Varian & Add-on Produk F&B',
    'headerTitle' => 'Varian & Add-on Produk (Modifiers)',
    'headerSubtitle' => 'Konfigurasi opsi varian, tingkat rasa, topping tambahan, dan integrasi pemotongan stok bahan baku resep',
])

@section('content')
<div class="max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
    showAddGroupModal: false,
    showEditGroupModal: false,
    showAddOptionModal: false,
    showEditOptionModal: false,

    deleteGroupModalOpen: false,
    deleteGroupTarget: { id: '', name: '' },
    deleteOptionModalOpen: false,
    deleteOptionTarget: { id: '', name: '' },

    selectedGroup: null,
    groupForm: { id: '', name: '', description: '', selection_type: 'single', min_selection: 0, max_selection: 1, is_required: false, sort_order: 0, product_ids: [] },

    selectedOption: null,
    optionForm: {
        id: '',
        group_id: '',
        name: '',
        price_delta: 0,
        affects_material: false,
        sort_order: 0,
        is_active: true,
        materials: []
    },

    openAddOption(group) {
        this.selectedGroup = group;
        this.optionForm = {
            id: '',
            group_id: group.id,
            name: '',
            price_delta: 0,
            affects_material: false,
            sort_order: 0,
            is_active: true,
            materials: []
        };
        this.showAddOptionModal = true;
    },

    openEditOption(group, opt) {
        this.selectedGroup = group;
        this.selectedOption = opt;
        this.optionForm = {
            id: opt.id,
            group_id: group.id,
            name: opt.name,
            price_delta: Number(opt.price_delta || 0),
            affects_material: Boolean(opt.affects_material),
            sort_order: Number(opt.sort_order || 0),
            is_active: Boolean(opt.is_active),
            materials: (opt.materials || []).map(m => ({
                material_id: m.material_id,
                quantity: Number(m.quantity || 0),
                unit_id: m.unit_id || ''
            }))
        };
        this.showEditOptionModal = true;
    },

    openEditGroup(group) {
        this.selectedGroup = group;
        this.groupForm = {
            id: group.id,
            name: group.name,
            description: group.description || '',
            selection_type: group.selection_type,
            min_selection: Number(group.min_selection || 0),
            max_selection: Number(group.max_selection || 1),
            is_required: Boolean(group.is_required),
            sort_order: Number(group.sort_order || 0),
            product_ids: (group.products || []).map(p => p.id)
        };
        this.showEditGroupModal = true;
    },

    addMaterialRow() {
        this.optionForm.materials.push({
            material_id: '',
            quantity: 1,
            unit_id: ''
        });
    },

    removeMaterialRow(idx) {
        this.optionForm.materials.splice(idx, 1);
    },

    openDeleteGroup(id, name) {
        this.deleteGroupTarget = { id, name };
        this.deleteGroupModalOpen = true;
    },
    closeDeleteGroup() {
        this.deleteGroupModalOpen = false;
        this.deleteGroupTarget = { id: '', name: '' };
    },
    submitDeleteGroup() {
        if (this.deleteGroupTarget.id) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = '{{ url('pos/modifiers/groups') }}/' + this.deleteGroupTarget.id;
            f.innerHTML = '<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'_method\' value=\'DELETE\'>';
            document.body.appendChild(f);
            f.submit();
        }
    },

    openDeleteOption(id, name) {
        this.deleteOptionTarget = { id, name };
        this.deleteOptionModalOpen = true;
    },
    closeDeleteOption() {
        this.deleteOptionModalOpen = false;
        this.deleteOptionTarget = { id: '', name: '' };
    },
    submitDeleteOption() {
        if (this.deleteOptionTarget.id) {
            const form = document.getElementById('form-delete-option-' + this.deleteOptionTarget.id);
            if (form) form.submit();
        }
    }
}">

    <!-- ===================================================== -->
    <!-- 0. UNIFIED SEGMENTED NAVIGATION (UI Unification)      -->
    <!-- ===================================================== -->
    <div class="flex items-center justify-between gap-4 overflow-x-auto no-scrollbar pb-1">
        <div class="inline-flex p-1 bg-black/[0.05] dark:bg-white/[0.08] rounded-[14px] border border-black/[0.04] dark:border-white/[0.06] shrink-0">
            <a href="{{ route('products.index') }}"
               class="px-4 py-2 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <span>Barang Fisik (Katalog)</span>
            </a>
            <a href="{{ route('services.index') }}"
               class="px-4 py-2 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.32 4.486c.049.58.025 1.193-.139 1.743" />
                </svg>
                <span>Jasa &amp; Layanan</span>
            </a>
            <a href="{{ route('pos.modifiers.index') }}"
               class="px-4 py-2 rounded-[10px] text-[13px] font-semibold bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm flex items-center gap-2 whitespace-nowrap transition-all">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Varian &amp; Modifiers</span>
            </a>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                              -->
    <!-- ===================================================== -->
    <header class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-7 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <div class="min-w-0 flex flex-col justify-center">
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('products.index') }}" class="hover:text-[#007AFF] transition-colors">Katalog Produk</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Varian &amp; Modifiers</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight leading-snug truncate">
                Varian &amp; Add-on Produk (Modifiers)
            </h1>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                Konfigurasi opsi ukuran cup, tingkat rasa, topping tambahan, dan integrasi konsumsi bahan baku resep
            </p>
        </div>

        @if(\App\Support\Context::hasPermission('pos.modifiers'))
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <button type="button" @click="showAddGroupModal = true"
                class="h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-semibold flex items-center justify-center gap-2 transition shadow-sm shadow-[#007AFF]/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Buat Grup Modifier</span>
            </button>
        </div>
        @endif
    </header>

    <!-- ===================================================== -->
    <!-- 2. MODIFIERS GROUP LIST                               -->
    <!-- ===================================================== -->
    @if($groups->isEmpty())
    <div class="p-12 text-center rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-dashed border-black/15 dark:border-white/15 space-y-3.5">
        <div class="w-14 h-14 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <h3 class="font-bold text-[17px] text-black dark:text-white tracking-tight">Belum Ada Grup Modifier</h3>
        <p class="text-[13px] text-black/50 dark:text-white/50 max-w-md mx-auto leading-relaxed">
            Buat grup modifier (seperti Ukuran Cup, Extra Shot Espresso, Topping Boba, atau Level Pedas) untuk mempermudah operasional kasir dan etalase.
        </p>
        @if(\App\Support\Context::hasPermission('pos.modifiers'))
        <button type="button" @click="showAddGroupModal = true"
            class="h-10 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold inline-flex items-center gap-2 shadow-sm">
            <span>Buat Grup Pertama</span>
        </button>
        @endif
    </div>
    @else
    <div class="space-y-6">
        @foreach($groups as $group)
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">
            <!-- Group Header Bar -->
            <div class="p-5 bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">{{ $group->name }}</h2>

                        <!-- Selection Type Pill -->
                        @if($group->selection_type === \App\Models\ModifierGroup::SELECTION_SINGLE)
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                Single Choice (Pilih 1)
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] border border-[#AF52DE]/20">
                                Multiple Choice (Bisa Banyak)
                            </span>
                        @endif

                        <!-- Required Badge -->
                        @if($group->is_required)
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                                Wajib Diisi
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.04] dark:bg-white/[0.08] text-black/50 dark:text-white/50">
                                Opsional
                            </span>
                        @endif

                        <span class="text-[11px] text-black/40 dark:text-white/40 tabular-nums">
                            Min: {{ $group->min_selection }} · Max: {{ $group->max_selection }}
                        </span>
                    </div>

                    @if($group->description)
                        <p class="text-[12.5px] text-black/60 dark:text-white/60 mt-1">{{ $group->description }}</p>
                    @endif

                    <!-- Assigned Products Pill List -->
                    <div class="flex items-center gap-1.5 flex-wrap mt-2.5">
                        <span class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider">Diterapkan pada:</span>
                        @forelse($group->products as $p)
                            <span class="px-2.5 py-0.5 rounded-[6px] text-[11px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 border border-black/5 dark:border-white/5">
                                {{ $p->name }}
                            </span>
                        @empty
                            <span class="text-[11.5px] italic text-black/40 dark:text-white/40">Belum ditautkan ke produk manapun.</span>
                        @endforelse
                    </div>
                </div>

                @if(\App\Support\Context::hasPermission('pos.modifiers'))
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="openAddOption({{ json_encode($group) }})"
                        class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-semibold flex items-center gap-1.5 shadow-sm transition active:scale-[0.98]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Tambah Opsi</span>
                    </button>

                    <button type="button" @click="openEditGroup({{ json_encode($group) }})"
                        class="h-9 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[12.5px] font-medium text-black/80 dark:text-white/80 transition active:scale-[0.98]">
                        Edit Grup
                    </button>
                </div>
                @endif
            </div>

            <!-- Options Table (Desktop & Tablet) -->
            <div class="hidden sm:block overflow-x-auto scrollbar-thin">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-black/[0.015] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-5">Nama Opsi</th>
                            <th class="py-3 px-4">Tambahan Biaya</th>
                            <th class="py-3 px-4">Konsumsi Bahan Baku Resep</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($group->options as $opt)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                            <td class="py-3.5 px-5 font-semibold text-black dark:text-white text-[13.5px]">
                                {{ $opt->name }}
                            </td>
                            <td class="py-3.5 px-4 tabular-nums font-medium text-black/80 dark:text-white/80">
                                @if($opt->price_delta > 0)
                                    <span class="text-[#34C759] dark:text-[#30D158] font-bold">+Rp {{ number_format($opt->price_delta, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-black/40 dark:text-white/40">+Rp 0</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($opt->affects_material && $opt->materials->isNotEmpty())
                                    <div class="flex flex-col gap-1">
                                        @foreach($opt->materials as $mat)
                                            <span class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-black/70 dark:text-white/70">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                                                {{ $mat->material?->name ?? 'Material' }}:
                                                <strong class="text-black dark:text-white tabular-nums">{{ (float)$mat->quantity }} {{ $mat->unit?->name ?? $mat->material?->unit?->name ?? 'Unit' }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                @elseif($opt->affects_material)
                                    <span class="text-[11.5px] text-[#FF9500] font-medium italic">Perlu bahan (belum dipetakan)</span>
                                @else
                                    <span class="text-[11.5px] text-black/40 dark:text-white/40">Tanpa potong stok bahan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($opt->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#34C759] dark:text-[#30D158]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-black/40 dark:text-white/40">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                @if(\App\Support\Context::hasPermission('pos.modifiers'))
                                <button type="button" @click="openEditOption({{ json_encode($group) }}, {{ json_encode($opt) }})"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors mr-1">
                                    Edit
                                </button>
                                <button type="button" @click="openDeleteOption('{{ $opt->id }}', '{{ addslashes($opt->name) }}')"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors">
                                    Hapus
                                </button>
                                <form id="form-delete-option-{{ $opt->id }}" method="POST" action="{{ route('pos.modifiers.options.destroy', $opt->id) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-[13px] text-black/40 dark:text-white/40">
                                Belum ada opsi varian pada grup ini. Klik "Tambah Opsi" di atas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Options Mobile Inset Cards (< sm) -->
            <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @forelse($group->options as $opt)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-[14px] font-semibold text-black dark:text-white">{{ $opt->name }}</h4>
                            <p class="text-[12.5px] mt-0.5 tabular-nums">
                                @if($opt->price_delta > 0)
                                    <span class="text-[#34C759] font-bold">+Rp {{ number_format($opt->price_delta, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-black/40 dark:text-white/40">+Rp 0</span>
                                @endif
                            </p>
                        </div>
                        @if(\App\Support\Context::hasPermission('pos.modifiers'))
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" @click="openEditOption({{ json_encode($group) }}, {{ json_encode($opt) }})"
                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10">
                                Edit
                            </button>
                            <button type="button" @click="openDeleteOption('{{ $opt->id }}', '{{ addslashes($opt->name) }}')"
                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] bg-[#FF3B30]/10">
                                Hapus
                            </button>
                        </div>
                        @endif
                    </div>

                    @if($opt->affects_material && $opt->materials->isNotEmpty())
                    <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-[11.5px] space-y-1">
                        @foreach($opt->materials as $mat)
                            <div class="flex items-center justify-between text-black/70 dark:text-white/70">
                                <span>{{ $mat->material?->name ?? 'Material' }}:</span>
                                <strong class="tabular-nums text-black dark:text-white">{{ (float)$mat->quantity }} {{ $mat->unit?->name ?? $mat->material?->unit?->name ?? 'Unit' }}</strong>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-6 text-center text-[12.5px] text-black/40 dark:text-white/40">
                    Belum ada opsi varian pada grup ini.
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: BUAT / EDIT MODIFIER GROUP                         -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.modifiers'))
    <div x-show="showAddGroupModal || showEditGroupModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/30 backdrop-blur-md"
         @keydown.escape.window="showAddGroupModal = false; showEditGroupModal = false;">
        <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
             @click.outside="showAddGroupModal = false; showEditGroupModal = false;">

            <!-- Mobile Grab Bar -->
            <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

            <!-- Sticky Top Header -->
            <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-10 h-10 rounded-[12px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate"
                            x-text="showEditGroupModal ? 'Edit Grup Modifier' : 'Buat Grup Modifier Baru'"></h3>
                        <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate">Atur aturan pilihan, minimum/maksimum opsi, dan tautkan ke menu</p>
                    </div>
                </div>
                <button type="button" @click="showAddGroupModal = false; showEditGroupModal = false;"
                    class="w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Form Content -->
            <form method="POST" :action="showEditGroupModal ? '{{ url('pos/modifiers/groups') }}/' + groupForm.id : '{{ route('pos.modifiers.groups.store') }}'"
                class="flex-1 overflow-y-auto p-6 sm:p-8 overscroll-contain sidebar-scroll flex flex-col justify-between space-y-5">
                @csrf
                <template x-if="showEditGroupModal">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-4">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Nama Grup Modifier <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" x-model="groupForm.name" required placeholder="Contoh: Ukuran Cup, Tingkat Manis, Extra Topping"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Deskripsi / Petunjuk Pelanggan</label>
                        <input type="text" name="description" x-model="groupForm.description" placeholder="Contoh: Pilih 1 ukuran yang Anda sukai"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Tipe Pilihan <span class="text-[#FF3B30]">*</span></label>
                            <select name="selection_type" x-model="groupForm.selection_type"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <option value="single">Single Choice (Hanya 1 Opsi)</option>
                                <option value="multiple">Multiple Choice (Bisa Pilih Banyak)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Kewajiban Memilih <span class="text-[#FF3B30]">*</span></label>
                            <select name="is_required" x-model="groupForm.is_required"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <option :value="false">Opsional (Boleh Dilewati)</option>
                                <option :value="true">Wajib Diisi (Required)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Minimum Pilihan</label>
                            <input type="number" name="min_selection" x-model.number="groupForm.min_selection" min="0" max="20"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Maksimum Pilihan</label>
                            <input type="number" name="max_selection" x-model.number="groupForm.max_selection" min="1" max="50"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Products Multi-Select Assignment -->
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                        <label class="block font-semibold text-black/80 dark:text-white/80 text-[13px]">Tautkan pada Produk (Bulk Assignment)</label>
                        <p class="text-[11.5px] text-black/45 dark:text-white/45">Centang produk yang akan menampilkan opsi modifier ini saat dipesan di kasir/web.</p>
                        <div class="max-h-40 overflow-y-auto p-2 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-1 text-[12.5px]">
                            @foreach($products as $prod)
                            <label class="flex items-center gap-2.5 p-2 rounded-[8px] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] cursor-pointer">
                                <input type="checkbox" name="product_ids[]" value="{{ $prod->id }}" :checked="groupForm.product_ids.includes('{{ $prod->id }}')"
                                    class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                <span class="text-black dark:text-white font-medium">{{ $prod->name }}</span>
                                <span class="text-[11px] text-black/40 dark:text-white/40 ml-auto tabular-nums">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="sticky bottom-0 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 flex items-center justify-between gap-3 shrink-0">
                    <div>
                        <template x-if="showEditGroupModal">
                            <button type="button" @click="openDeleteGroup(groupForm.id, groupForm.name)"
                                class="h-11 px-4 rounded-[12px] text-[13px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 transition active:scale-[0.98]">
                                Hapus Grup
                            </button>
                        </template>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="showAddGroupModal = false; showEditGroupModal = false;"
                            class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98]">
                            Batal
                        </button>
                        <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: TAMBAH / EDIT OPSI MODIFIER & MATERIAL MAPPING     -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.modifiers'))
    <div x-show="showAddOptionModal || showEditOptionModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/30 backdrop-blur-md"
         @keydown.escape.window="showAddOptionModal = false; showEditOptionModal = false;">
        <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
             @click.outside="showAddOptionModal = false; showEditOptionModal = false;">

            <!-- Mobile Grab Bar -->
            <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

            <!-- Sticky Top Header -->
            <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate"
                            x-text="showEditOptionModal ? 'Edit Opsi Modifier' : 'Tambah Opsi Modifier Baru'"></h3>
                        <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate"
                            x-text="'Grup: ' + (selectedGroup ? selectedGroup.name : '')"></p>
                    </div>
                </div>
                <button type="button" @click="showAddOptionModal = false; showEditOptionModal = false;"
                    class="w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Form Content -->
            <form method="POST" :action="showEditOptionModal ? '{{ url('pos/modifiers/options') }}/' + optionForm.id : '{{ url('pos/modifiers/groups') }}/' + (selectedGroup ? selectedGroup.id : '') + '/options'"
                class="flex-1 overflow-y-auto p-6 sm:p-8 overscroll-contain sidebar-scroll flex flex-col justify-between space-y-5">
                @csrf
                <template x-if="showEditOptionModal">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-4">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Nama Opsi Varian / Add-on <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" x-model="optionForm.name" required placeholder="Contoh: Small, Large, Extra Shot, Oat Milk"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Tambahan Biaya (Rp) <span class="text-[#FF3B30]">*</span></label>
                            <input type="number" name="price_delta" x-model.number="optionForm.price_delta" min="0" step="500" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <span class="text-[11px] text-black/40 dark:text-white/40 mt-1 block">Ketik 0 jika gratis / tanpa biaya tambahan</span>
                        </div>

                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Potong Stok Bahan Baku Resep?</label>
                            <select name="affects_material" x-model="optionForm.affects_material"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <option value="0" :value="false">Tidak (Hanya variasi harga, tanpa potong stok)</option>
                                <option value="1" :value="true">Ya (Kurangi bahan baku resep dari gudang)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Material Recipe Mapping Section -->
                    <div x-show="optionForm.affects_material" class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="text-[13px] font-bold text-black dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <span>Pemetaan Bahan Baku Resep</span>
                            </div>
                            <button type="button" @click="addMaterialRow()" class="text-[12px] font-semibold text-[#007AFF] hover:underline">
                                + Tambah Bahan
                            </button>
                        </div>

                        <p class="text-[11.5px] text-black/50 dark:text-white/50">Tentukan bahan baku yang otomatis dipotong saat opsi ini dipilih oleh pembeli.</p>

                        <div class="space-y-2.5">
                            <template x-for="(matRow, idx) in optionForm.materials" :key="idx">
                                <div class="flex items-center gap-2.5">
                                    <select :name="'materials[' + idx + '][material_id]'" x-model="matRow.material_id" required
                                        class="flex-1 h-10 px-3 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach($materials as $mat)
                                            <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit?->name ?? 'Unit' }})</option>
                                        @endforeach
                                    </select>

                                    <input type="number" :name="'materials[' + idx + '][quantity]'" x-model.number="matRow.quantity" step="0.0001" min="0.0001" placeholder="Jumlah" required
                                        class="w-24 h-10 px-3 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">

                                    <select :name="'materials[' + idx + '][unit_id]'" x-model="matRow.unit_id"
                                        class="w-24 h-10 px-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                        <option value="">Satuan</option>
                                        @foreach($units as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>

                                    <button type="button" @click="removeMaterialRow(idx)"
                                        class="w-8 h-8 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center shrink-0 transition" title="Hapus Baris">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </template>

                            <div x-show="optionForm.materials.length === 0" class="text-center py-3 text-[12px] text-black/40 dark:text-white/40">
                                Belum ada bahan baku ditambahkan. Klik "+ Tambah Bahan".
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="sticky bottom-0 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" @click="showAddOptionModal = false; showEditOptionModal = false;"
                        class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98]">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG (Hapus Grup Modifier)               -->
    <!-- ===================================================== -->
    <div x-show="deleteGroupModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm">
        <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
            @click.outside="closeDeleteGroup()">
            <div class="px-5 pt-6 pb-4">
                <div class="w-10 h-10 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>
                <h4 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Hapus Grup Modifier?</h4>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                    Grup <span x-text="deleteGroupTarget.name" class="font-semibold text-black dark:text-white"></span> beserta seluruh opsi di dalamnya akan dihapus.
                </p>
                <div class="mt-3 p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] text-[11px] text-black/50 dark:text-white/50 text-left flex items-start gap-1.5">
                    <svg class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                    <span>Tenang: Riwayat transaksi penjualan masa lalu yang menggunakan varian ini tetap aman tersimpan.</span>
                </div>
            </div>
            <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.1] text-[15px] font-medium">
                <button type="button" @click="closeDeleteGroup()" class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.1] active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDeleteGroup()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG (Hapus Opsi Modifier)               -->
    <!-- ===================================================== -->
    <div x-show="deleteOptionModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm">
        <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
            @click.outside="closeDeleteOption()">
            <div class="px-5 pt-6 pb-4">
                <div class="w-10 h-10 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>
                <h4 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Hapus Opsi Varian?</h4>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                    Opsi <span x-text="deleteOptionTarget.name" class="font-semibold text-black dark:text-white"></span> akan dihapus dari grup ini.
                </p>
            </div>
            <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.1] text-[15px] font-medium">
                <button type="button" @click="closeDeleteOption()" class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.1] active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDeleteOption()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
