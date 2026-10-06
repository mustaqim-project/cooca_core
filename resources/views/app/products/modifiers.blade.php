@extends('layouts.app', [
    'title' => __('products.modifiers_title'),
    'headerTitle' => __('products.modifiers_title'),
    'headerSubtitle' => __('products.modifiers_subtitle'),
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
                quantity: Number(m.quantity || 1),
                unit_id: m.unit_id || ''
            }))
        };
        this.showEditOptionModal = true;
    },

    openAddGroup() {
        this.groupForm = { id: '', name: '', description: '', selection_type: 'single', min_selection: 0, max_selection: 1, is_required: false, sort_order: 0, product_ids: [] };
        this.showAddGroupModal = true;
    },

    openEditGroup(group) {
        this.selectedGroup = group;
        this.groupForm = {
            id: group.id,
            name: group.name,
            description: group.description || '',
            selection_type: group.selection_type || 'single',
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

    removeMaterialRow(index) {
        this.optionForm.materials.splice(index, 1);
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
            const form = document.getElementById('form-delete-group-' + this.deleteGroupTarget.id);
            if (form) {
                form.submit();
            }
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
    <!-- 1. TOOLBAR / PAGE HEADER                              -->
    <!-- ===================================================== -->
    <x-module-header
        :title="__('products.modifiers_title')"
        :subtitle="__('products.modifiers_subtitle')"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => __('products.page_title'), 'url' => route('products.index')],
            ['label' => __('products.breadcrumb_modifiers'), 'url' => null],
        ]">
        @if(\App\Support\Context::hasPermission('pos.modifiers'))
        <button type="button" @click="openAddGroup()"
            class="h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-semibold flex items-center justify-center gap-2 transition shadow-sm shadow-[#007AFF]/25 shrink-0 min-w-[44px]">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>{{ __('products.btn_create_group') }}</span>
        </button>
        @endif
    </x-module-header>

    <!-- ===================================================== -->
    <!-- 2. UNIFIED SEGMENTED NAVIGATION (UI Unification)      -->
    <!-- ===================================================== -->
    <x-module-tabs module="products" />

    <!-- ===================================================== -->
    <!-- 3. MODIFIERS GROUP LIST                               -->
    <!-- ===================================================== -->
    @if($groups->isEmpty())
    <div class="p-12 text-center rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-dashed border-black/15 dark:border-white/15 space-y-3.5 shadow-sm">
        <div class="w-14 h-14 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center mx-auto">
            <i data-lucide="sliders" class="w-7 h-7"></i>
        </div>
        <h3 class="font-bold text-[17px] text-black dark:text-white tracking-tight">{{ __('products.empty_modifier_groups') }}</h3>
        <p class="text-[13px] text-black/50 dark:text-white/50 max-w-md mx-auto leading-relaxed">
            {{ __('products.empty_modifier_groups_desc') }}
        </p>
        @if(\App\Support\Context::hasPermission('pos.modifiers'))
        <button type="button" @click="openAddGroup()"
            class="h-10 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold inline-flex items-center gap-2 shadow-sm min-w-[44px]">
            <span>{{ __('products.btn_create_first_group') }}</span>
        </button>
        @endif
    </div>
    @else
    <div class="space-y-6">
        @foreach($groups as $group)
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">
            <!-- Group Header Bar with Clean Bento Typography (Anti-Pill Abuse) -->
            <div class="p-5 bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">{{ $group->name }}</h2>

                        <div class="flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50">
                            <span>·</span>
                            <span>{{ $group->selection_type === \App\Models\ModifierGroup::SELECTION_SINGLE ? __('products.badge_single_choice') : __('products.badge_multiple_choice') }}</span>
                            <span>·</span>
                            <span class="{{ $group->is_required ? 'text-[#FF3B30] font-medium' : 'text-black/50 dark:text-white/50' }}">
                                {{ $group->is_required ? __('products.badge_required') : __('products.badge_optional') }}
                            </span>
                            <span>·</span>
                            <span class="tabular-nums">{{ __('products.selection_min_max', ['min' => $group->min_selection, 'max' => $group->max_selection]) }}</span>
                        </div>
                    </div>

                    @if($group->description)
                        <p class="text-[12.5px] text-black/60 dark:text-white/60 mt-1">{{ $group->description }}</p>
                    @endif

                    <!-- Assigned Products List -->
                    <div class="flex items-center gap-1.5 flex-wrap mt-2.5">
                        <span class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider">{{ __('products.applied_to_products') }}</span>
                        @forelse($group->products as $p)
                            <span class="px-2.5 py-0.5 rounded-[6px] text-[11px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 border border-black/5 dark:border-white/5">
                                {{ $p->name }}
                            </span>
                        @empty
                            <span class="text-[11.5px] italic text-black/40 dark:text-white/40">{{ __('products.no_products_linked') }}</span>
                        @endforelse
                    </div>
                </div>

                @if(\App\Support\Context::hasPermission('pos.modifiers'))
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="openAddOption({{ json_encode($group) }})"
                        class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-semibold flex items-center gap-1.5 shadow-sm transition active:scale-[0.98] min-w-[44px]">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>{{ __('products.btn_add_option') }}</span>
                    </button>

                    <button type="button" @click="openEditGroup({{ json_encode($group) }})"
                        class="h-9 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[12.5px] font-medium text-black/80 dark:text-white/80 transition active:scale-[0.98] min-w-[44px]">
                        {{ __('products.btn_edit_group') }}
                    </button>

                    <form id="form-delete-group-{{ $group->id }}" method="POST" action="{{ route('pos.modifiers.groups.destroy', $group->id) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
                @endif
            </div>

            <!-- Options Table (Desktop & Tablet) -->
            <div class="hidden sm:block overflow-x-auto scrollbar-thin">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-black/[0.015] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-5">{{ __('products.th_option_name') }}</th>
                            <th class="py-3 px-4">{{ __('products.th_price_delta') }}</th>
                            <th class="py-3 px-4">{{ __('products.th_material_consumption') }}</th>
                            <th class="py-3 px-4">{{ __('products.th_status') }}</th>
                            <th class="py-3 px-5 text-right">{{ __('products.th_actions') }}</th>
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
                                    <span class="text-[#34C759] dark:text-[#30D158] font-bold">+{{ $business->currency_symbol }} {{ number_format($opt->price_delta, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-black/40 dark:text-white/40">+{{ $business->currency_symbol }} 0</span>
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
                                    <span class="text-[11.5px] text-[#FF9500] font-medium italic">{{ __('products.material_needs_mapping') }}</span>
                                @else
                                    <span class="text-[11.5px] text-black/40 dark:text-white/40">{{ __('products.no_material_deduction') }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($opt->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#34C759] dark:text-[#30D158]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('products.status_active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-black/40 dark:text-white/40">
                                        {{ __('products.status_inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                @if(\App\Support\Context::hasPermission('pos.modifiers'))
                                <button type="button" @click="openEditOption({{ json_encode($group) }}, {{ json_encode($opt) }})"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors mr-1 min-w-[44px]">
                                    {{ __('products.btn_edit') }}
                                </button>
                                <button type="button" @click="openDeleteOption('{{ $opt->id }}', '{{ addslashes($opt->name) }}')"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors min-w-[44px]">
                                    {{ __('products.btn_delete') }}
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
                                {{ __('products.empty_options_in_group') }}
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
                                    <span class="text-[#34C759] font-bold">+{{ $business->currency_symbol }} {{ number_format($opt->price_delta, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-black/40 dark:text-white/40">+{{ $business->currency_symbol }} 0</span>
                                @endif
                            </p>
                        </div>
                        @if(\App\Support\Context::hasPermission('pos.modifiers'))
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" @click="openEditOption({{ json_encode($group) }}, {{ json_encode($opt) }})"
                                class="h-9 px-3 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 min-w-[44px]">
                                {{ __('products.btn_edit') }}
                            </button>
                            <button type="button" @click="openDeleteOption('{{ $opt->id }}', '{{ addslashes($opt->name) }}')"
                                class="h-9 px-3 rounded-[8px] text-[12px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 min-w-[44px]">
                                {{ __('products.btn_delete') }}
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
                    {{ __('products.empty_options_in_group') }}
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: BUAT / EDIT MODIFIER GROUP (Teleport to Body)      -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.modifiers'))
    <template x-teleport="body">
        <div x-show="showAddGroupModal || showEditGroupModal" x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
             @keydown.escape.window="showAddGroupModal = false; showEditGroupModal = false;">
            <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                 @click.outside="showAddGroupModal = false; showEditGroupModal = false;">

                <!-- Mobile Grab Bar -->
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

                <!-- Sticky Top Header -->
                <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-[12px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center shrink-0">
                            <i data-lucide="sliders" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate"
                                x-text="showEditGroupModal ? '{{ __('products.modal_edit_group_title') }}' : '{{ __('products.modal_create_group_title') }}'"></h3>
                            <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate">{{ __('products.modal_group_sub') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddGroupModal = false; showEditGroupModal = false;"
                        class="w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0 min-w-[36px]">
                        <i data-lucide="x" class="w-4 h-4"></i>
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
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_group_name') }} <span class="text-[#FF3B30]">*</span></label>
                            <input type="text" name="name" x-model="groupForm.name" required placeholder="{{ __('products.placeholder_group_name') }}"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_group_desc') }}</label>
                            <input type="text" name="description" x-model="groupForm.description" placeholder="{{ __('products.placeholder_group_desc') }}"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_selection_type') }} <span class="text-[#FF3B30]">*</span></label>
                                <select name="selection_type" x-model="groupForm.selection_type"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="single">{{ __('products.option_single_choice') }}</option>
                                    <option value="multiple">{{ __('products.option_multiple_choice') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_is_required') }} <span class="text-[#FF3B30]">*</span></label>
                                <select name="is_required" x-model="groupForm.is_required"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="0" :value="false">{{ __('products.option_optional') }}</option>
                                    <option value="1" :value="true">{{ __('products.option_required') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_min_selection') }}</label>
                                <input type="number" name="min_selection" x-model.number="groupForm.min_selection" min="0" max="20"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_max_selection') }}</label>
                                <input type="number" name="max_selection" x-model.number="groupForm.max_selection" min="1" max="50"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                        </div>

                        <!-- Products Multi-Select Assignment -->
                        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                            <label class="block font-semibold text-black/80 dark:text-white/80 text-[13px]">{{ __('products.label_bulk_product_assignment') }}</label>
                            <p class="text-[11.5px] text-black/45 dark:text-white/45">{{ __('products.sub_bulk_product_assignment') }}</p>
                            <div class="max-h-40 overflow-y-auto p-2 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-1 text-[12.5px]">
                                @foreach($products as $prod)
                                <label class="flex items-center gap-2.5 p-2 rounded-[8px] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] cursor-pointer">
                                    <input type="checkbox" name="product_ids[]" value="{{ $prod->id }}" :checked="groupForm.product_ids.includes('{{ $prod->id }}')"
                                        class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black dark:text-white font-medium">{{ $prod->name }}</span>
                                    <span class="text-[11px] text-black/40 dark:text-white/40 ml-auto tabular-nums">{{ $business->currency_symbol }} {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
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
                                    class="h-11 px-4 rounded-[12px] text-[13px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 transition active:scale-[0.98] min-w-[44px]">
                                    {{ __('products.btn_delete_group') }}
                                </button>
                            </template>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" @click="showAddGroupModal = false; showEditGroupModal = false;"
                                class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98] min-w-[44px]">
                                {{ __('products.btn_cancel') }}
                            </button>
                            <button type="submit"
                                class="h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25 min-w-[44px]">
                                {{ __('products.btn_save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: TAMBAH / EDIT OPSI MODIFIER (Teleport to Body)     -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.modifiers'))
    <template x-teleport="body">
        <div x-show="showAddOptionModal || showEditOptionModal" x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
             @keydown.escape.window="showAddOptionModal = false; showEditOptionModal = false;">
            <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                 @click.outside="showAddOptionModal = false; showEditOptionModal = false;">

                <!-- Mobile Grab Bar -->
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

                <!-- Sticky Top Header -->
                <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate"
                                x-text="showEditOptionModal ? '{{ __('products.modal_edit_option_title') }}' : '{{ __('products.modal_create_option_title') }}'"></h3>
                            <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate"
                                x-text="'Grup: ' + (selectedGroup ? selectedGroup.name : '')"></p>
                        </div>
                    </div>
                    <button type="button" @click="showAddOptionModal = false; showEditOptionModal = false;"
                        class="w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0 min-w-[36px]">
                        <i data-lucide="x" class="w-4 h-4"></i>
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
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_option_name') }} <span class="text-[#FF3B30]">*</span></label>
                            <input type="text" name="name" x-model="optionForm.name" required placeholder="{{ __('products.placeholder_option_name') }}"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_price_delta') }} <span class="text-[#FF3B30]">*</span></label>
                                <input type="number" name="price_delta" x-model.number="optionForm.price_delta" min="0" step="500" required
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <span class="text-[11px] text-black/40 dark:text-white/40 mt-1 block">{{ __('products.sub_price_delta_zero') }}</span>
                            </div>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_affects_material') }}</label>
                                <select name="affects_material" x-model="optionForm.affects_material"
                                    @change="if(!Boolean(optionForm.affects_material) || optionForm.affects_material === '0' || optionForm.affects_material === false) { optionForm.materials = []; }"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="0" :value="false">{{ __('products.affects_material_no') }}</option>
                                    <option value="1" :value="true">{{ __('products.affects_material_yes') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Material Recipe Mapping Section -->
                        <template x-if="Boolean(optionForm.affects_material) && optionForm.affects_material !== '0'">
                            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-[13px] font-bold text-black dark:text-white flex items-center gap-2">
                                        <i data-lucide="boxes" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span>{{ __('products.section_material_recipe_mapping') }}</span>
                                    </div>
                                    <button type="button" @click="addMaterialRow()" class="text-[12px] font-semibold text-[#007AFF] hover:underline">
                                        + {{ __('products.btn_add_material') }}
                                    </button>
                                </div>

                                <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ __('products.sub_material_recipe_mapping') }}</p>

                                <div class="space-y-2.5">
                                    <template x-for="(matRow, idx) in optionForm.materials" :key="idx">
                                        <div class="flex items-center gap-2.5">
                                            <select :name="'materials[' + idx + '][material_id]'" x-model="matRow.material_id" required
                                                class="flex-1 h-10 px-3 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                                <option value="">{{ __('products.placeholder_select_material_option') }}</option>
                                                @foreach($materials as $mat)
                                                    <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit?->name ?? 'Unit' }})</option>
                                                @endforeach
                                            </select>

                                            <input type="number" :name="'materials[' + idx + '][quantity]'" x-model.number="matRow.quantity" step="0.0001" min="0.0001" placeholder="{{ __('products.placeholder_quantity') }}" required
                                                class="w-24 h-10 px-3 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">

                                            <select :name="'materials[' + idx + '][unit_id]'" x-model="matRow.unit_id"
                                                class="w-24 h-10 px-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                                <option value="">{{ __('products.placeholder_unit') }}</option>
                                                @foreach($units as $u)
                                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>

                                            <button type="button" @click="removeMaterialRow(idx)"
                                                class="w-8 h-8 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center shrink-0 transition" title="Hapus Baris">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </template>

                                    <div x-show="optionForm.materials.length === 0" class="text-center py-3 text-[12px] text-black/40 dark:text-white/40">
                                        {{ __('products.empty_bom') }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Sticky Footer -->
                    <div class="sticky bottom-0 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 flex items-center justify-end gap-3 shrink-0">
                        <button type="button" @click="showAddOptionModal = false; showEditOptionModal = false;"
                            class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98] min-w-[44px]">
                            {{ __('products.btn_cancel') }}
                        </button>
                        <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25 min-w-[44px]">
                            {{ __('products.btn_save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG (Hapus Grup Modifier - Teleport)    -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="deleteGroupModalOpen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
            <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                @click.outside="closeDeleteGroup()">
                <div class="px-5 pt-6 pb-4">
                    <div class="w-10 h-10 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('products.btn_delete_group') }}?</h4>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                        Grup <span x-text="deleteGroupTarget.name" class="font-semibold text-black dark:text-white"></span> {{ __('products.modal_delete_bom_desc') }}
                    </p>
                    <div class="mt-3 p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] text-[11px] text-black/50 dark:text-white/50 text-left flex items-start gap-1.5">
                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                        <span>{{ __('products.peace_of_mind_delete_product') }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.1] text-[15px] font-medium">
                    <button type="button" @click="closeDeleteGroup()" class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.1] active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('products.btn_cancel') }}
                    </button>
                    <button type="button" @click="submitDeleteGroup()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('products.btn_delete_confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG (Hapus Opsi Modifier - Teleport)    -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="deleteOptionModalOpen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
            <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                @click.outside="closeDeleteOption()">
                <div class="px-5 pt-6 pb-4">
                    <div class="w-10 h-10 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('products.btn_delete') }} Opsi?</h4>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                        Opsi <span x-text="deleteOptionTarget.name" class="font-semibold text-black dark:text-white"></span> {{ __('products.modal_delete_bom_desc') }}
                    </p>
                </div>
                <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.1] text-[15px] font-medium">
                    <button type="button" @click="closeDeleteOption()" class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.1] active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('products.btn_cancel') }}
                    </button>
                    <button type="button" @click="submitDeleteOption()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('products.btn_delete_confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
