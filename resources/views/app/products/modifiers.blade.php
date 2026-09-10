@extends('layouts.app', ['title' => 'Varian & Add-on Produk F&B'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAddGroupModal: false,
    showEditGroupModal: false,
    showAddOptionModal: false,
    showEditOptionModal: false,

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
    }
}">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.index') }}" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    Katalog Produk
                </a>
                <span class="text-xs text-black/30 dark:text-white/30">•</span>
                <span class="text-xs text-black/50 dark:text-white/50">F&amp;B Customization</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1">Varian &amp; Add-on Produk (Modifiers)</h1>
            <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">Konfigurasi opsi ukuran, tingkat rasa, topping tambahan, dan integrasi konsumsi bahan baku resep.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="showAddGroupModal = true" class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Buat Grup Modifier Baru</span>
            </button>
        </div>
    </div>

    <!-- Modifiers Group List -->
    @if($groups->isEmpty())
    <div class="p-12 text-center rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-dashed border-black/15 dark:border-white/15 space-y-3">
        <div class="w-14 h-14 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="font-semibold text-base text-black dark:text-white">Belum Ada Grup Modifier</div>
        <p class="text-xs text-black/50 dark:text-white/50 max-w-sm mx-auto">Buat grup modifier (seperti Ukuran Cup, Extra Shot, Topping Boba, atau Level Pedas) untuk menambahkan fleksibilitas pada menu Anda.</p>
        <button type="button" @click="showAddGroupModal = true" class="h-9 px-4 rounded-[10px] bg-[#007AFF] text-white text-xs font-semibold inline-flex items-center gap-2">
            <span>Buat Grup Pertama</span>
        </button>
    </div>
    @else
    <div class="space-y-6">
        @foreach($groups as $group)
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-sm overflow-hidden">
            <!-- Group Header Bar -->
            <div class="p-4 sm:p-5 bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/10 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base font-bold text-black dark:text-white">{{ $group->name }}</h2>

                        <!-- Selection Type Pill -->
                        @if($group->selection_type === \App\Models\ModifierGroup::SELECTION_SINGLE)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                Single Choice (Pilih 1)
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] border border-[#AF52DE]/20">
                                Multiple Choice (Bisa Banyak)
                            </span>
                        @endif

                        <!-- Required Badge -->
                        @if($group->is_required)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                                Wajib Diisi
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50">
                                Opsional
                            </span>
                        @endif

                        <span class="text-[11px] text-black/40 dark:text-white/40 tabular-nums">
                            Min: {{ $group->min_selection }} • Max: {{ $group->max_selection }}
                        </span>
                    </div>

                    @if($group->description)
                        <p class="text-xs text-black/60 dark:text-white/60 mt-1">{{ $group->description }}</p>
                    @endif

                    <!-- Assigned Products Pill List -->
                    <div class="flex items-center gap-1.5 flex-wrap mt-2">
                        <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Diterapkan pada:</span>
                        @forelse($group->products as $p)
                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 border border-black/5 dark:border-white/5">
                                {{ $p->name }}
                            </span>
                        @empty
                            <span class="text-[11px] italic text-black/40 dark:text-white/40">Belum ditautkan ke produk manapun.</span>
                        @endforelse
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="openAddOption({{ json_encode($group) }})" class="h-8 px-3 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Tambah Opsi</span>
                    </button>

                    <button type="button" @click="openEditGroup({{ json_encode($group) }})" class="h-8 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-medium text-black/80 dark:text-white/80 transition">
                        Edit Grup
                    </button>
                </div>
            </div>

            <!-- Options Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-black dark:text-white">
                    <thead class="bg-black/[0.01] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Nama Opsi</th>
                            <th class="py-3 px-4">Tambahan Harga</th>
                            <th class="py-3 px-4">Konsumsi Bahan Baku (Resep Modifier)</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($group->options as $opt)
                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.02] transition">
                            <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                {{ $opt->name }}
                            </td>
                            <td class="py-3 px-4 tabular-nums font-medium text-black/80 dark:text-white/80">
                                @if($opt->price_delta > 0)
                                    <span class="text-[#34C759] font-bold">+Rp {{ number_format($opt->price_delta, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-black/40 dark:text-white/40">+Rp 0</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($opt->affects_material && $opt->materials->isNotEmpty())
                                    <div class="flex flex-col gap-1">
                                        @foreach($opt->materials as $mat)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-black/70 dark:text-white/70">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                                                {{ $mat->material?->name ?? 'Material' }}:
                                                <strong class="text-black dark:text-white tabular-nums">{{ (float)$mat->quantity }} {{ $mat->unit?->name ?? $mat->material?->unit?->name ?? 'Unit' }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                @elseif($opt->affects_material)
                                    <span class="text-[11px] text-[#FF9500] italic">Memerlukan bahan (belum di-mapping)</span>
                                @else
                                    <span class="text-[11px] text-black/40 dark:text-white/40">Tanpa efek material</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($opt->is_active)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#34C759]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-black/40 dark:text-white/40">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button type="button" @click="openEditOption({{ json_encode($group) }}, {{ json_encode($opt) }})" class="text-xs text-[#007AFF] hover:underline font-semibold mr-3">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('pos.modifiers.options.destroy', $opt->id) }}" class="inline-block" onsubmit="return confirm('Hapus opsi varian {{ $opt->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-[#FF3B30] hover:underline">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-xs text-black/40 dark:text-white/40">
                                Belum ada opsi varian pada grup ini. Klik "Tambah Opsi" untuk menambahkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: BUAT / EDIT MODIFIER GROUP                         -->
    <!-- ========================================================= -->
    <div x-show="showAddGroupModal || showEditGroupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" style="display: none;">
        <div class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[18px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4 text-black dark:text-white max-h-[90vh] overflow-y-auto" @click.outside="showAddGroupModal = false; showEditGroupModal = false;">
            <div class="flex items-center justify-between pb-2 border-b border-black/10 dark:border-white/10">
                <h3 class="font-bold text-base" x-text="showEditGroupModal ? 'Edit Grup Modifier' : 'Buat Grup Modifier Baru'"></h3>
                <button type="button" @click="showAddGroupModal = false; showEditGroupModal = false;" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white">✕</button>
            </div>

            <form method="POST" :action="showEditGroupModal ? '{{ url('pos/modifiers/groups') }}/' + groupForm.id : '{{ route('pos.modifiers.groups.store') }}'" class="space-y-3.5">
                @csrf
                <template x-if="showEditGroupModal">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Nama Grup Modifier *</label>
                    <input type="text" name="name" x-model="groupForm.name" required placeholder="Contoh: Ukuran Minuman, Tingkat Manis, Extra Topping" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Deskripsi / Petunjuk Pelanggan</label>
                    <input type="text" name="description" x-model="groupForm.description" placeholder="Contoh: Pilih 1 ukuran yang Anda sukai" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Tipe Pilihan *</label>
                        <select name="selection_type" x-model="groupForm.selection_type" class="w-full h-10 px-2.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="single">Single Choice (Radio - Hanya 1)</option>
                            <option value="multiple">Multiple Choice (Checkbox - Banyak)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Kewajiban Memilih *</label>
                        <select name="is_required" x-model="groupForm.is_required" class="w-full h-10 px-2.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option :value="false">Opsional (Boleh Dilewati)</option>
                            <option :value="true">Wajib Diisi (Required)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Minimum Pilihan</label>
                        <input type="number" name="min_selection" x-model.number="groupForm.min_selection" min="0" max="20" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Maksimum Pilihan</label>
                        <input type="number" name="max_selection" x-model.number="groupForm.max_selection" min="1" max="50" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <!-- Products Multi-Select Assignment -->
                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Terapkan pada Produk (Bulk Assign)</label>
                    <div class="max-h-36 overflow-y-auto p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 space-y-1 text-xs">
                        @foreach($products as $prod)
                        <label class="flex items-center gap-2 p-1.5 rounded-[6px] hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer">
                            <input type="checkbox" name="product_ids[]" value="{{ $prod->id }}" :checked="groupForm.product_ids.includes('{{ $prod->id }}')" class="rounded border-black/20 text-[#007AFF] focus:ring-[#007AFF]/40">
                            <span class="text-black dark:text-white font-medium">{{ $prod->name }}</span>
                            <span class="text-[10px] text-black/40 dark:text-white/40 ml-auto tabular-nums">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between items-center pt-2 border-t border-black/10 dark:border-white/10">
                    <template x-if="showEditGroupModal">
                        <button type="button" @click="
                            if(confirm('Hapus grup modifier ini beserta seluruh opsinya?')) {
                                const f = document.createElement('form');
                                f.method = 'POST';
                                f.action = '{{ url('pos/modifiers/groups') }}/' + groupForm.id;
                                f.innerHTML = '<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'_method\' value=\'DELETE\'>';
                                document.body.appendChild(f);
                                f.submit();
                            }
                        " class="text-xs text-[#FF3B30] hover:underline font-semibold">
                            Hapus Grup
                        </button>
                    </template>
                    <span x-show="!showEditGroupModal"></span>

                    <div class="flex gap-2">
                        <button type="button" @click="showAddGroupModal = false; showEditGroupModal = false;" class="h-9 px-4 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">Batal</button>
                        <button type="submit" class="h-9 px-5 rounded-[10px] bg-[#007AFF] text-white text-xs font-semibold shadow-sm">Simpan Grup</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: TAMBAH / EDIT OPSI MODIFIER & MATERIAL MAPPING     -->
    <!-- ========================================================= -->
    <div x-show="showAddOptionModal || showEditOptionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" style="display: none;">
        <div class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[18px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4 text-black dark:text-white max-h-[90vh] overflow-y-auto" @click.outside="showAddOptionModal = false; showEditOptionModal = false;">
            <div class="flex items-center justify-between pb-2 border-b border-black/10 dark:border-white/10">
                <div>
                    <h3 class="font-bold text-base" x-text="showEditOptionModal ? 'Edit Opsi Modifier' : 'Tambah Opsi Modifier Baru'"></h3>
                    <div class="text-[11px] text-black/50 dark:text-white/50" x-text="'Grup: ' + (selectedGroup ? selectedGroup.name : '')"></div>
                </div>
                <button type="button" @click="showAddOptionModal = false; showEditOptionModal = false;" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white">✕</button>
            </div>

            <form method="POST" :action="showEditOptionModal ? '{{ url('pos/modifiers/options') }}/' + optionForm.id : '{{ url('pos/modifiers/groups') }}/' + (selectedGroup ? selectedGroup.id : '') + '/options'" class="space-y-3.5">
                @csrf
                <template x-if="showEditOptionModal">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Nama Opsi Varian / Add-on *</label>
                    <input type="text" name="name" x-model="optionForm.name" required placeholder="Contoh: Small, Large, Extra Shot, Oat Milk" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Tambahan Harga (Rp) *</label>
                        <input type="number" name="price_delta" x-model.number="optionForm.price_delta" min="0" step="500" required class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">Masukkan 0 jika tanpa biaya tambahan</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Efek Pengurangan Stok Bahan?</label>
                        <select name="affects_material" x-model="optionForm.affects_material" class="w-full h-10 px-2.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option :value="false">Tidak (Hanya Harga)</option>
                            <option :value="true">Ya (Kurangi Bahan Baku Resep)</option>
                        </select>
                    </div>
                </div>

                <!-- Material Recipe Mapping Section -->
                <div x-show="optionForm.affects_material" class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <span>Mapping Bahan Baku Resep</span>
                        </div>
                        <button type="button" @click="addMaterialRow()" class="text-[11px] font-bold text-[#007AFF] hover:underline">
                            + Tambah Bahan
                        </button>
                    </div>

                    <p class="text-[11px] text-black/50 dark:text-white/50">Tentukan bahan baku yang dikonsumsi ketika pelanggan memilih opsi ini.</p>

                    <div class="space-y-2">
                        <template x-for="(matRow, idx) in optionForm.materials" :key="idx">
                            <div class="flex items-center gap-2">
                                <select :name="'materials[' + idx + '][material_id]'" x-model="matRow.material_id" required class="flex-1 h-9 px-2 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none">
                                    <option value="">-- Pilih Bahan Baku --</option>
                                    @foreach($materials as $mat)
                                        <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit?->name ?? 'Unit' }})</option>
                                    @endforeach
                                </select>

                                <input type="number" :name="'materials[' + idx + '][quantity]'" x-model.number="matRow.quantity" step="0.0001" min="0.0001" placeholder="Jumlah" required class="w-24 h-9 px-2 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-semibold tabular-nums text-black dark:text-white focus:outline-none">

                                <select :name="'materials[' + idx + '][unit_id]'" x-model="matRow.unit_id" class="w-24 h-9 px-1.5 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[11px] text-black dark:text-white focus:outline-none">
                                    <option value="">Satuan</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>

                                <button type="button" @click="removeMaterialRow(idx)" class="w-7 h-7 rounded-[6px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center font-bold text-xs">
                                    ✕
                                </button>
                            </div>
                        </template>

                        <div x-show="optionForm.materials.length === 0" class="text-center py-2 text-[11px] text-black/40 dark:text-white/40">
                            Belum ada bahan baku ditambahkan. Klik "+ Tambah Bahan".
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showAddOptionModal = false; showEditOptionModal = false;" class="h-9 px-4 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">Batal</button>
                    <button type="submit" class="h-9 px-5 rounded-[10px] bg-[#007AFF] text-white text-xs font-semibold shadow-sm">Simpan Opsi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
