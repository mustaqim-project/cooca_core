@extends('layouts.app', [
    'title' => 'Kontrol Akses & Role Tim',
    'headerTitle' => 'Kontrol Akses & Role Tim (RBAC)',
    'headerSubtitle' => 'Kelola wewenang peran, delegasikan operasional kasir & gudang, dan lindungi integritas data bisnis.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    activeTab: '{{ (\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage')) ? 'roles' : ((\App\Support\Context::hasPermission('users.view') || \App\Support\Context::hasPermission('users.manage')) ? 'members' : 'matrix') }}',
    showGuide: true,
    showCreateModal: false,
    showMemberRoleModal: false,
    editingRole: null,
    editingPreset: false,
    editFormAction: '',
    searchPermission: '',
    selectedPermissions: [],
    editingMember: null,
    memberRoleAction: '',
    memberSelectedRoleId: '',

    // Delete Role Dialog State
    deleteRoleModalOpen: false,
    deleteRoleTarget: { id: null, name: '' },
    openDeleteRole(id, name) {
        this.deleteRoleTarget = { id, name };
        this.deleteRoleModalOpen = true;
    },
    closeDeleteRole() {
        this.deleteRoleModalOpen = false;
        this.deleteRoleTarget = { id: null, name: '' };
    },
    submitDeleteRole() {
        if (this.deleteRoleTarget.id) {
            document.getElementById('form-delete-role-' + this.deleteRoleTarget.id).submit();
        }
    },

    // Remove Member Dialog State
    deleteMemberModalOpen: false,
    deleteMemberTarget: { id: null, name: '' },
    openDeleteMember(id, name) {
        this.deleteMemberTarget = { id, name };
        this.deleteMemberModalOpen = true;
    },
    closeDeleteMember() {
        this.deleteMemberModalOpen = false;
        this.deleteMemberTarget = { id: null, name: '' };
    },
    submitDeleteMember() {
        if (this.deleteMemberTarget.id) {
            document.getElementById('form-delete-member-' + this.deleteMemberTarget.id).submit();
        }
    },

    openCreateModal() {
        this.editingRole = null;
        this.editingPreset = false;
        this.selectedPermissions = [];
        this.showCreateModal = true;
    },

    openEditModal(role, actionUrl, permSlugs) {
        this.editingRole = role;
        this.editingPreset = false;
        this.editFormAction = actionUrl;
        this.selectedPermissions = [...permSlugs];
        this.showCreateModal = true;
    },

    openEditPresetModal(role, actionUrl, permSlugs) {
        this.editingRole = role;
        this.editingPreset = true;
        this.editFormAction = actionUrl;
        this.selectedPermissions = [...permSlugs];
        this.showCreateModal = true;
    },

    openChangeMemberRoleModal(member, actionUrl, currentRoleId) {
        this.editingMember = member;
        this.memberRoleAction = actionUrl;
        this.memberSelectedRoleId = currentRoleId;
        this.showMemberRoleModal = true;
    },

    toggleCategory(categorySlugs, checked) {
        if (checked) {
            categorySlugs.forEach(slug => {
                if (!this.selectedPermissions.includes(slug)) {
                    this.selectedPermissions.push(slug);
                }
            });
        } else {
            this.selectedPermissions = this.selectedPermissions.filter(slug => !categorySlugs.includes(slug));
        }
    },

    isCategoryAllSelected(categorySlugs) {
        return categorySlugs.every(slug => this.selectedPermissions.includes(slug));
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Pengaturan</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Kontrol Akses (RBAC)</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Kontrol Akses &amp; Role Tim</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Wewenang staf operasional, pemisahan hak kasir &amp; gudang, serta proteksi margin laba.</p>
        </div>

        <!-- Toolbar Actions & Primary Button -->
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('roles.create') || \App\Support\Context::hasPermission('roles.manage'))
            <button type="button" @click="openCreateModal()"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Buat Role Custom</span>
            </button>
            @endif
        </div>
    </header>

    <!-- Flash Notifications (Apple Banner Style) -->
    @if(session('success'))
        <div class="rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
            <div class="flex-1 font-medium">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
            <div class="flex-1 font-medium">{{ session('error') }}</div>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (Flat Neutral Material, Apple Standard) -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total Role -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Role Terdaftar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $roles->count() }}</span>
                <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Preset &amp; Custom</span>
            </div>
        </div>

        <!-- Tile 2: Anggota Tim Aktif -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Anggota Tim Aktif</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $members->count() }}</span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Karyawan Toko</span>
            </div>
        </div>

        <!-- Tile 3: Cakupan Permissions -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Granular Permissions</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ collect($permissions)->flatten(1)->count() }}</span>
                <span class="text-[11px] font-medium text-[#5856D6] dark:text-[#5E5CE6]">Modul Sistem</span>
            </div>
        </div>

        <!-- Tile 4: Keamanan Data & HPP -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Proteksi Keamanan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[18px] font-bold text-[#AF52DE] dark:text-[#BF5AF2]">RBAC Aktif</span>
                <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Multi-User</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. SEGMENTED NAVIGATION & VIEW SWITCHER               -->
    <!-- ===================================================== -->
    <div class="flex items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
            @if(\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage'))
            <button type="button" @click="activeTab = 'roles'"
                :class="activeTab === 'roles' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                class="px-3.5 py-1.5 rounded-[7px] transition-all flex items-center gap-1.5">
                <span>Daftar Role</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-semibold bg-black/10 dark:bg-white/10 tabular-nums">{{ $roles->count() }}</span>
            </button>
            @endif

            @if(\App\Support\Context::hasPermission('users.view') || \App\Support\Context::hasPermission('users.manage'))
            <button type="button" @click="activeTab = 'members'"
                :class="activeTab === 'members' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                class="px-3.5 py-1.5 rounded-[7px] transition-all flex items-center gap-1.5">
                <span>Anggota Tim</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-semibold bg-black/10 dark:bg-white/10 tabular-nums">{{ $members->count() }}</span>
            </button>
            @endif

            @if(\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage'))
            <button type="button" @click="activeTab = 'matrix'"
                :class="activeTab === 'matrix' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                class="px-3.5 py-1.5 rounded-[7px] transition-all flex items-center gap-1.5">
                <span>Matriks Hak Akses</span>
            </button>
            @endif
        </div>

        <button type="button" @click="showGuide = !showGuide" class="text-[12px] text-[#007AFF] hover:underline font-medium flex items-center gap-1">
            <span x-text="showGuide ? 'Sembunyikan Panduan' : 'Lihat Panduan RBAC'"></span>
        </button>
    </div>

    <!-- Interactive Apple Callout: Panduan RBAC -->
    <div x-show="showGuide" x-transition class="rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-3">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
            <h2 class="text-[13px] font-semibold uppercase tracking-wider text-black/60 dark:text-white/60">Prinsip Keamanan Role &amp; Hak Akses (RBAC)</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1">
                <span class="text-[11px] font-semibold text-[#007AFF]">1. Hak Istimewa Owner</span>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">Pemilik usaha (Owner) memiliki wewenang penuh tanpa batas ke seluruh modul dan keuangan.</p>
            </div>
            <div class="rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1">
                <span class="text-[11px] font-semibold text-[#34C759]">2. Preset vs Custom</span>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">Gunakan preset default (Kasir, Staff Gudang) atau susun role custom sesuai kebutuhan toko.</p>
            </div>
            <div class="rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1">
                <span class="text-[11px] font-semibold text-[#FF9500]">3. Tautkan Akun Karyawan</span>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">Tambahkan akun login staf di tab <em>Anggota Tim</em>. Role dapat diubah seketika kapan saja.</p>
            </div>
            <div class="rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1">
                <span class="text-[11px] font-semibold text-[#AF52DE]">4. Proteksi Margin &amp; HPP</span>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">Nonaktifkan izin <code>costing.view_margin</code> pada staf agar modal resep &amp; margin tetap rahasia.</p>
            </div>
        </div>
    </div>

    @if(\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage'))
    <!-- ===================================================== -->
    <!-- TAB 1: DAFTAR ROLE CARDS                              -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'roles'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $role)
            @php
                $isCustom = (bool) $role->business_id;
                $permSlugs = $role->permissions->pluck('slug')->toArray();
                $memberCount = $role->memberships()->count();
            @endphp
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between gap-4">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-[16px] font-semibold text-black dark:text-white">{{ $role->name }}</h3>
                                @if($isCustom)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">
                                        Custom
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                        Preset Sistem
                                    </span>
                                @endif
                            </div>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-1 line-clamp-2 leading-relaxed">
                                {{ $role->description ?: 'Tidak ada deskripsi tambahan untuk peran ini.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Role Stats -->
                    <div class="flex items-center gap-4 text-[12px] text-black/50 dark:text-white/50 pt-3 border-t border-black/[0.04] dark:border-white/[0.06]">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold tabular-nums text-black dark:text-white">{{ $role->permissions->count() }}</span>
                            <span>hak akses</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold tabular-nums text-black dark:text-white">{{ $memberCount }}</span>
                            <span>anggota</span>
                        </div>
                    </div>

                    <!-- Permissions Pill Preview -->
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">Cakupan Izin:</span>
                        <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto pr-1">
                            @forelse($role->permissions->take(8) as $perm)
                                <span class="text-[11px] px-2 py-0.5 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 tabular-nums">
                                    {{ \Illuminate\Support\Str::after($perm->slug, '.') }}
                                </span>
                            @empty
                                <span class="text-[11px] text-black/40 dark:text-white/40 italic">Belum ada hak akses yang ditugaskan.</span>
                            @endforelse
                            @if($role->permissions->count() > 8)
                                <span class="text-[11px] px-1.5 py-0.5 rounded-[6px] bg-black/[0.08] dark:bg-white/[0.1] text-black/70 dark:text-white/70 font-semibold tabular-nums">
                                    +{{ $role->permissions->count() - 8 }} lainnya
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card Actions -->
                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-2">
                    @if($isCustom)
                        @if(\App\Support\Context::hasPermission('roles.edit') || \App\Support\Context::hasPermission('roles.manage'))
                        <button type="button"
                            @click="openEditModal({{ Js::from($role) }}, '{{ route('roles.update', $role) }}', {{ Js::from($permSlugs) }})"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            <span>Edit Permission</span>
                        </button>
                        @endif

                        @if(\App\Support\Context::hasPermission('roles.delete') || \App\Support\Context::hasPermission('roles.manage'))
                        <button type="button"
                            @click="openDeleteRole({{ $role->id }}, '{{ addslashes($role->name) }}')"
                            class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center"
                            title="Hapus Role">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>

                        <form id="form-delete-role-{{ $role->id }}" method="POST" action="{{ route('roles.destroy', $role) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endif
                    @else
                        <div class="flex items-center justify-between gap-2 w-full">
                            <span class="text-[12px] text-[#FF9500] flex items-center gap-1.5 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                Nama terlindungi
                            </span>
                            @if(\App\Support\Context::hasPermission('roles.edit') || \App\Support\Context::hasPermission('roles.manage'))
                            <button type="button"
                                @click="openEditPresetModal({{ Js::from($role) }}, '{{ route('roles.update', $role) }}', {{ Js::from($permSlugs) }})"
                                class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                                <span>Edit Permission</span>
                            </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('users.view') || \App\Support\Context::hasPermission('users.manage'))
    <!-- ===================================================== -->
    <!-- TAB 2: ANGGOTA TIM & TAMBAH KARYAWAN                  -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'members'" class="space-y-6" style="display: none;">
        @if(\App\Support\Context::hasPermission('users.create') || \App\Support\Context::hasPermission('users.manage'))
        <!-- Form Tambah Karyawan Baru -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                <h2 class="text-[17px] font-semibold text-black dark:text-white">Tambah Anggota Tim Baru</h2>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Buat akun login untuk kasir, kepala gudang, atau staf operasional bisnis Anda.</p>
            </div>

            <form method="POST" action="{{ route('roles.members.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                @csrf
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Karyawan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Email Login <span class="text-[#FF3B30]">*</span></label>
                    <input type="email" name="email" required placeholder="budi@kedai.com"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Password Awal</label>
                    <input type="password" name="password" minlength="6" placeholder="Default: password123"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Role / Jabatan <span class="text-[#FF3B30]">*</span></label>
                    <select name="role_id" required
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption->id }}">
                                {{ $roleOption->name }}{{ $roleOption->business_id ? ' (Custom)' : ' (Preset)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="h-11 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.765z" />
                    </svg>
                    <span>Tambah Karyawan</span>
                </button>
            </form>
        </div>
        @endif

        <!-- Tabel Daftar Karyawan Aktif -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="p-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Daftar Pengguna Aktif di {{ $business->name }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Akun dengan wewenang akses aktif ke workspace bisnis ini.</p>
                </div>
                <span class="text-[12px] font-medium text-black/60 dark:text-white/60 tabular-nums">
                    {{ $members->count() }} Anggota
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                            <th class="py-2.5 px-4">Nama Pengguna</th>
                            <th class="py-2.5 px-4">Email Login</th>
                            <th class="py-2.5 px-4">Role / Wewenang</th>
                            @if(\App\Support\Context::hasPermission('users.edit') || \App\Support\Context::hasPermission('users.delete') || \App\Support\Context::hasPermission('users.manage'))
                            <th class="py-2.5 px-4 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($members as $member)
                        @php
                            $memberRoleId = $member->role_id ?? $roles->firstWhere('slug', $member->role)?->id ?? '';
                        @endphp
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-[12px] uppercase shrink-0">
                                        {{ substr($member->user?->name ?? 'U', 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="font-medium text-black dark:text-white">{{ $member->user?->name }}</span>
                                        @if($member->role === 'owner')
                                            <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#AF52DE]/12 text-[#AF52DE]">Owner</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60">{{ $member->user?->email }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-[6px] bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 font-medium text-[12px]">
                                    {{ $member->customRole?->name ?? ucfirst($member->role) }}
                                </span>
                            </td>
                            @if(\App\Support\Context::hasPermission('users.edit') || \App\Support\Context::hasPermission('users.delete') || \App\Support\Context::hasPermission('users.manage'))
                            <td class="py-3 px-4 text-right">
                                @if($member->role !== 'owner')
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(\App\Support\Context::hasPermission('users.edit') || \App\Support\Context::hasPermission('users.manage'))
                                        <button type="button"
                                            @click="openChangeMemberRoleModal({{ Js::from($member) }}, '{{ route('roles.members.role', $member) }}', '{{ $memberRoleId }}')"
                                            class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center gap-1">
                                            <span>Ubah Role</span>
                                        </button>
                                        @endif

                                        @if(\App\Support\Context::hasPermission('users.delete') || \App\Support\Context::hasPermission('users.manage'))
                                        <button type="button"
                                            @click="openDeleteMember({{ $member->id }}, '{{ addslashes($member->user?->name ?? 'Karyawan') }}')"
                                            class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center"
                                            title="Keluarkan Anggota">
                                            Hapus
                                        </button>

                                        <form id="form-delete-member-{{ $member->id }}" method="POST" action="{{ route('roles.members.destroy', $member->id) }}" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] text-black/40 dark:text-white/40 italic">Owner Utama</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage'))
    <!-- ===================================================== -->
    <!-- TAB 3: MATRIKS HAK AKSES LENGKAP                      -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'matrix'" class="space-y-4" style="display: none;">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-[17px] font-semibold text-black dark:text-white">Matriks Hak Akses &amp; Modul Sistem</h2>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Daftar seluruh wewenang granular sistem yang dapat dikonfigurasikan pada role custom.</p>
            </div>

            <!-- Search Permission Filter -->
            <div class="relative w-full sm:w-64">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" x-model="searchPermission"
                    placeholder="Cari izin / modul..."
                    class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($permissions as $category => $categoryPermissions)
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <span class="text-[12px] font-semibold text-black dark:text-white uppercase tracking-wider">
                        {{ $category ?: 'Modul Umum' }}
                    </span>
                    <span class="text-[11px] tabular-nums text-black/40 dark:text-white/40">{{ count($categoryPermissions) }} item</span>
                </div>

                <div class="space-y-1.5">
                    @foreach($categoryPermissions as $permission)
                    <div class="p-2.5 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5"
                        x-show="!searchPermission || '{{ strtolower($permission->name . ' ' . $permission->slug) }}'.includes(searchPermission.toLowerCase())">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0 mt-1.5"></span>
                        <div class="min-w-0">
                            <div class="text-[13px] font-medium text-black dark:text-white">{{ $permission->name }}</div>
                            <div class="text-[11px] font-mono text-black/40 dark:text-white/40 mt-0.5">{{ $permission->slug }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('roles.create') || \App\Support\Context::hasPermission('roles.edit') || \App\Support\Context::hasPermission('roles.manage'))
    <!-- ===================================================== -->
    <!-- MODAL 1: CREATE / EDIT CUSTOM ROLE (macOS Sheet)       -->
    <!-- ===================================================== -->
    <div x-show="showCreateModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl w-full max-w-2xl max-h-[90vh] rounded-[18px] border border-black/10 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col"
            @click.away="showCreateModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <!-- Sheet Header -->
            <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white"
                        x-text="editingRole ? (editingPreset ? 'Edit Permission: ' + editingRole.name : 'Edit Role: ' + editingRole.name) : 'Buat Role Custom Baru'"></h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50"
                        x-text="editingPreset ? 'Hak akses preset role dapat diubah. Nama role sistem tidak dapat dimodifikasi.' : 'Tentukan kombinasi hak akses yang diizinkan untuk peran ini.'"></p>
                </div>
                <button type="button" @click="showCreateModal = false" class="w-8 h-8 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center text-black/50 dark:text-white/50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form :action="editingRole ? editFormAction : '{{ route('roles.store') }}'" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <template x-if="editingRole">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Nama Role
                                <span class="text-[#FF3B30]" x-show="!editingPreset">*</span>
                            </label>
                            <input type="text" name="name"
                                :required="!editingPreset"
                                :value="editingRole ? editingRole.name : ''"
                                :disabled="editingPreset"
                                :class="editingPreset ? 'opacity-50 cursor-not-allowed' : ''"
                                placeholder="Contoh: Kasir Shift Pagi"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <p class="text-[11px] text-[#FF9500] mt-1 flex items-center gap-1" x-show="editingPreset">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                </svg>
                                Nama role sistem terlindungi
                            </p>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Deskripsi Peran</label>
                            <input type="text" name="description"
                                :value="editingRole ? editingRole.description : ''"
                                :disabled="editingPreset"
                                :class="editingPreset ? 'opacity-50 cursor-not-allowed' : ''"
                                placeholder="Ringkasan tanggung jawab..."
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Permissions Selection -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <label class="text-[13px] font-semibold text-black dark:text-white">Pilih Hak Akses (Permissions) <span class="text-[#FF3B30]">*</span></label>
                            <span class="text-[12px] font-semibold text-[#007AFF] tabular-nums" x-text="selectedPermissions.length + ' hak akses dipilih'"></span>
                        </div>

                        <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                            @foreach($permissions as $category => $categoryPermissions)
                            @php
                                $categorySlugs = $categoryPermissions->pluck('slug')->toArray();
                            @endphp
                            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[12px] font-semibold uppercase tracking-wide text-black/70 dark:text-white/70">
                                        {{ $category ?: 'Lainnya' }}
                                    </span>
                                    <button type="button"
                                        @click="toggleCategory({{ Js::from($categorySlugs) }}, !isCategoryAllSelected({{ Js::from($categorySlugs) }}))"
                                        class="text-[11px] text-[#007AFF] hover:underline font-semibold">
                                        <span x-text="isCategoryAllSelected({{ Js::from($categorySlugs) }}) ? 'Batal Semua' : 'Pilih Semua'"></span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($categoryPermissions as $permission)
                                    <label class="flex items-start gap-2.5 p-2 rounded-[8px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#007AFF]/30 cursor-pointer transition select-none">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->slug }}"
                                            x-model="selectedPermissions"
                                            class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                        <div class="min-w-0">
                                            <span class="font-medium text-black dark:text-white text-[12px] block">{{ $permission->name }}</span>
                                            <span class="text-[10px] font-mono text-black/40 dark:text-white/40 block truncate">{{ $permission->slug }}</span>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-3.5 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2">
                    <button type="button" @click="showCreateModal = false"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        <span x-text="editingPreset ? 'Simpan Permission' : (editingRole ? 'Simpan Perubahan' : 'Buat Role Custom')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('users.edit') || \App\Support\Context::hasPermission('users.manage'))
    <!-- ===================================================== -->
    <!-- MODAL 2: CHANGE MEMBER ROLE (macOS Sheet)              -->
    <!-- ===================================================== -->
    <div x-show="showMemberRoleModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl w-full max-w-md rounded-[18px] border border-black/10 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col"
            @click.away="showMemberRoleModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Ubah Role Karyawan</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50" x-text="editingMember ? (editingMember.user ? editingMember.user.name : 'Anggota') : ''"></p>
                </div>
                <button type="button" @click="showMemberRoleModal = false" class="w-8 h-8 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center text-black/50 dark:text-white/50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="memberRoleAction" method="POST" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Pilih Role / Jabatan Baru <span class="text-[#FF3B30]">*</span></label>
                    <select name="role_id" required x-model="memberSelectedRoleId"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption->id }}">
                                {{ $roleOption->name }}{{ $roleOption->business_id ? ' (Custom)' : ' (Preset)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 p-3 text-[12px] text-[#007AFF] leading-relaxed">
                    Perubahan wewenang akan langsung aktif saat karyawan mengakses menu atau memproses transaksi di Cooca UMKM.
                </div>

                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2">
                    <button type="button" @click="showMemberRoleModal = false"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('roles.delete') || \App\Support\Context::hasPermission('roles.manage'))
    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG: HAPUS ROLE                        -->
    <!-- ===================================================== -->
    <div x-show="deleteRoleModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeDeleteRole()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Role?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Role <span x-text="deleteRoleTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus. Pastikan anggota dengan role ini telah dialihkan.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDeleteRole()" class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDeleteRole()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('users.delete') || \App\Support\Context::hasPermission('users.manage'))
    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG: KELUARKAN ANGGOTA                 -->
    <!-- ===================================================== -->
    <div x-show="deleteMemberModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeDeleteMember()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Keluarkan Anggota?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Akses <span x-text="deleteMemberTarget.name" class="font-medium text-black dark:text-white"></span> ke workspace bisnis ini akan langsung dicabut.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDeleteMember()" class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDeleteMember()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Keluarkan
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
