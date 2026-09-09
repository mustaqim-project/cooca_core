@extends('layouts.app', [
    'title' => 'Kontrol Akses & Role Tim',
    'headerTitle' => 'Kontrol Akses & Role Tim (RBAC)',
    'headerSubtitle' => 'Kelola wewenang peran, delegasikan operasional kasir & gudang, dan lindungi integritas data bisnis.'
])

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{
    activeTab: 'roles',
    showGuide: true,
    showCreateModal: false,
    showMemberRoleModal: false,
    editingRole: null,
    editFormAction: '',
    searchPermission: '',
    selectedPermissions: [],
    editingMember: null,
    memberRoleAction: '',
    memberSelectedRoleId: '',

    openCreateModal() {
        this.editingRole = null;
        this.selectedPermissions = [];
        this.showCreateModal = true;
    },

    openEditModal(role, actionUrl, permSlugs) {
        this.editingRole = role;
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

    <!-- Standard Breadcrumb & Executive Page Header -->
    <nav class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800/80" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-emerald-500 rounded px-1">
                    Dashboard
                </a>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <span class="text-slate-900 dark:text-slate-200 font-semibold" aria-current="page">Kontrol Akses &amp; Role</span>
            </li>
            <li class="hidden sm:inline text-slate-400 dark:text-slate-600" aria-hidden="true">•</li>
            <li class="hidden sm:inline">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                    RBAC Security
                </span>
            </li>
        </ol>
        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-medium">
                <i data-lucide="shield" class="w-3.5 h-3.5 text-cyan-500"></i>
                <span>{{ $roles->count() }} Role Terdaftar</span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-medium">
                <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-500"></i>
                <span>{{ $members->count() }} Anggota Tim</span>
            </span>
        </div>
    </nav>

    <!-- Top Action Bar & Tabs Switcher (Dedicated Standalone RBAC Panel) -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-3">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="activeTab = 'roles'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border"
                    :class="activeTab === 'roles'
                        ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-500/30 shadow-sm'
                        : 'bg-transparent text-slate-600 dark:text-slate-400 border-transparent hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                <i data-lucide="shield" class="w-4 h-4"></i>
                <span>Daftar Role & Hak Akses ({{ $roles->count() }})</span>
            </button>

            <button type="button" @click="activeTab = 'members'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border"
                    :class="activeTab === 'members'
                        ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-500/30 shadow-sm'
                        : 'bg-transparent text-slate-600 dark:text-slate-400 border-transparent hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Anggota Tim Aktif ({{ $members->count() }})</span>
            </button>

            <button type="button" @click="activeTab = 'matrix'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border"
                    :class="activeTab === 'matrix'
                        ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-500/30 shadow-sm'
                        : 'bg-transparent text-slate-600 dark:text-slate-400 border-transparent hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                <i data-lucide="grid" class="w-4 h-4"></i>
                <span>Matriks Permission Lengkap</span>
            </button>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" @click="openCreateModal()"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm transition flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Role Custom</span>
            </button>
        </div>
    </div>

    <!-- Panduan Penggunaan Fitur (Interactive Explainer Banner) -->
    <div class="rounded-2xl p-5 border border-indigo-200/80 dark:border-indigo-900/40 bg-gradient-to-r from-blue-50/70 to-indigo-50/70 dark:from-indigo-950/20 dark:to-slate-900/40 space-y-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5 text-indigo-700 dark:text-indigo-300">
                <div class="p-1.5 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Panduan Pengaturan Role & Hak Akses (RBAC)</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Prinsip keamanan data dan pemisahan wewenang operasional bisnis.</p>
                </div>
            </div>
            <button type="button" @click="showGuide = !showGuide" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 flex items-center gap-1 font-semibold">
                <span x-text="showGuide ? 'Sembunyikan Panduan' : 'Lihat Panduan'"></span>
                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': showGuide}"></i>
            </button>
        </div>

        <div x-show="showGuide" x-transition class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 pt-1">
            <!-- Step 1 -->
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 space-y-1.5 shadow-sm">
                <div class="flex items-center gap-2 text-blue-600 dark:text-cyan-400 font-bold text-xs">
                    <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-cyan-500/20 flex items-center justify-center text-[10px]">1</span>
                    <span>Hak Istimewa Owner</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                    Pemilik bisnis (Owner) memiliki hak otomatis ke seluruh modul, pengaturan, subscription, dan laporan laba tanpa batasan.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 space-y-1.5 shadow-sm">
                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-[10px]">2</span>
                    <span>Preset vs Custom Role</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                    Gunakan <strong>Preset Sistem</strong> (Kasir, Staff Gudang, Admin) atau buat <strong>Role Custom</strong> bila ingin kombinasi hak akses tertentu.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 space-y-1.5 shadow-sm">
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-bold text-xs">
                    <span class="w-5 h-5 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-[10px]">3</span>
                    <span>Tautkan Karyawan</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                    Tambahkan karyawan pada tab <em>Anggota Tim</em> lalu pilih role. Anda bisa mengubah role kapan saja dengan tombol <em>Ubah Role</em>.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 space-y-1.5 shadow-sm">
                <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 font-bold text-xs">
                    <span class="w-5 h-5 rounded-full bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center text-[10px]">4</span>
                    <span>Proteksi Laba & HPP</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                    Nonaktifkan hak <code>costing.view_margin</code> pada kasir/staf agar rincian margin laba dan modal resep tetap rahasia.
                </p>
            </div>
        </div>
    </div>

    <!-- Tab 1: Daftar Role Cards -->
    <div x-show="activeTab === 'roles'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($roles as $role)
            @php
                $isCustom = (bool) $role->business_id;
                $permSlugs = $role->permissions->pluck('slug')->toArray();
                $memberCount = $role->memberships()->count();
            @endphp
            <div class="rounded-2xl p-5 border {{ $isCustom ? 'border-cyan-300 dark:border-cyan-500/40 bg-cyan-50/20 dark:bg-cyan-950/10' : 'border-slate-200/90 dark:border-slate-800/90 bg-white dark:bg-slate-900/90' }} shadow-sm hover:shadow-md transition flex flex-col justify-between gap-4">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-900 dark:text-white text-base">{{ $role->name }}</h3>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isCustom ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-500/20 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-500/30' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                                    {{ $isCustom ? 'Custom Bisnis' : 'Preset Sistem' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                                {{ $role->description ?: 'Tidak ada deskripsi tambahan untuk peran ini.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Role Stats -->
                    <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400 pt-3 border-t border-slate-100 dark:border-slate-800/80 mt-3">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="key" class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400"></i>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $role->permissions->count() }}</span>
                            <span>hak akses</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $memberCount }}</span>
                            <span>karyawan</span>
                        </div>
                    </div>

                    <!-- Permissions Pill Preview -->
                    <div class="mt-3.5 space-y-1.5">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-500 dark:text-slate-400">Cakupan Izin Utama:</div>
                        <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto pr-1">
                            @forelse($role->permissions->take(8) as $perm)
                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 text-slate-700 dark:text-slate-300 font-mono">
                                    {{ \Illuminate\Support\Str::after($perm->slug, '.') }}
                                </span>
                            @empty
                                <span class="text-[11px] text-slate-400 italic">Belum ada hak akses yang ditugaskan.</span>
                            @endforelse
                            @if($role->permissions->count() > 8)
                                <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-slate-200/80 dark:bg-slate-700/80 text-slate-700 dark:text-slate-300 font-mono font-semibold">
                                    +{{ $role->permissions->count() - 8 }} lainnya
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card Actions -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    @if($isCustom)
                        <button type="button"
                                @click="openEditModal({{ Js::from($role) }}, '{{ route('roles.update', $role) }}', {{ Js::from($permSlugs) }})"
                                class="px-3 py-1.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 dark:bg-cyan-500/15 dark:hover:bg-cyan-500/25 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-500/30 text-xs font-bold transition flex items-center gap-1.5">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>Edit Permission</span>
                        </button>

                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin menghapus role custom ini? Anggota dengan role ini harus dipindahkan terlebih dahulu.', 'Hapus Role?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition" title="Hapus Role">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    @else
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 italic flex items-center gap-1">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i> Preset default terlindungi
                        </span>
                        <button type="button" @click="activeTab = 'matrix'" class="text-xs text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold transition">
                            Lihat Matriks &rarr;
                        </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Tab 2: Anggota Tim & Tambah Karyawan -->
    <div x-show="activeTab === 'members'" class="space-y-6">
        <!-- Form Tambah Karyawan -->
        <section class="bg-white dark:bg-slate-900/90 rounded-2xl p-6 border border-slate-200/90 dark:border-slate-800/90 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white text-base">Tambah Anggota Tim Baru</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Buat akun login untuk kasir, kepala gudang, atau staf operasional toko Anda.</p>
                </div>
                <span class="text-[10px] px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 font-bold uppercase tracking-wider">
                    Akun Karyawan
                </span>
            </div>

            <form method="POST" action="{{ route('roles.members.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nama Karyawan *</label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-600 transition" placeholder="Contoh: Budi Santoso">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Login *</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-600 transition" placeholder="budi@kedai.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Password Awal</label>
                    <input type="password" name="password" minlength="6" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-600 transition" placeholder="Default: password123">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Role / Jabatan *</label>
                    <select name="role_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-slate-900 dark:text-white text-xs transition">
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption->id }}">
                                {{ $roleOption->name }}{{ $roleOption->business_id ? ' (Custom)' : ' (Preset)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Tambah Karyawan</span>
                </button>
            </form>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                Catatan: Karyawan yang didaftarkan dapat langsung login di alamat <code>/login</code> dengan email dan password di atas.
            </p>
        </section>

        <!-- Tabel Daftar Karyawan Aktif -->
        <div class="bg-white dark:bg-slate-900/90 rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Daftar Pengguna Aktif di {{ $business->name }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Daftar seluruh akun yang memiliki akses ke workspace bisnis ini.</p>
                </div>
                <span class="text-xs font-bold font-mono px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    {{ $members->count() }} Anggota
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800/80">
                            <th class="py-3 px-3">Nama Pengguna</th>
                            <th class="py-3 px-3">Email Akun</th>
                            <th class="py-3 px-3">Role / Hak Akses</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @foreach($members as $member)
                        @php
                            $memberRoleId = $member->role_id ?? $roles->firstWhere('slug', $member->role)?->id ?? '';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-300 dark:border-emerald-500/30 flex items-center justify-center font-bold text-emerald-800 dark:text-emerald-300 uppercase text-xs">
                                        {{ substr($member->user?->name ?? 'U', 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $member->user?->name }}</span>
                                        @if($member->role === 'owner')
                                            <span class="ml-1.5 text-[10px] px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-300 border border-purple-200 dark:border-purple-500/30 font-bold">Owner</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-600 dark:text-slate-300">{{ $member->user?->email }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-cyan-300 font-semibold text-[11px]">
                                    {{ $member->customRole?->name ?? ucfirst($member->role) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($member->role !== 'owner')
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                @click="openChangeMemberRoleModal({{ Js::from($member) }}, '{{ route('roles.members.role', $member) }}', '{{ $memberRoleId }}')"
                                                class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-cyan-500/15 dark:hover:bg-cyan-500/25 text-slate-700 dark:text-cyan-300 border border-slate-200 dark:border-cyan-500/30 text-xs font-bold transition flex items-center gap-1.5">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                            <span>Ubah Role</span>
                                        </button>

                                        <form method="POST" action="{{ route('roles.members.destroy', $member->id) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Keluarkan anggota ini dari bisnis? Akses ke workspace bisnis akan langsung dicabut.', 'Keluarkan Anggota?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition" title="Keluarkan Anggota">
                                                <i data-lucide="user-x" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Owner Utama (Terlindungi)</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Matriks Hak Akses Lengkap -->
    <div x-show="activeTab === 'matrix'" class="bg-white dark:bg-slate-900/90 rounded-2xl p-6 border border-slate-200/90 dark:border-slate-800/90 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-slate-900 dark:text-white text-base">Matriks Hak Akses & Modul Sistem</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar seluruh permission granular sistem yang dapat dikonfigurasikan pada role custom.</p>
            </div>

            <!-- Search Permission Filter -->
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="text" x-model="searchPermission"
                       placeholder="Cari izin / modul..."
                       class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 rounded-xl text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-600 transition">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($permissions as $category => $categoryPermissions)
            <div class="rounded-2xl bg-slate-50/70 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 p-4 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-2 text-emerald-700 dark:text-cyan-300 text-xs font-bold uppercase tracking-wider">
                        <i data-lucide="layers" class="w-4 h-4 text-emerald-600 dark:text-cyan-400"></i>
                        <span>{{ $category ?: 'Modul Umum' }}</span>
                    </div>
                    <span class="text-[10px] font-mono text-slate-500">{{ count($categoryPermissions) }} item</span>
                </div>

                <div class="space-y-2">
                    @foreach($categoryPermissions as $permission)
                    <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/50 flex items-start gap-2.5 transition hover:border-slate-300 dark:hover:border-slate-700"
                         x-show="!searchPermission || '{{ strtolower($permission->name . ' ' . $permission->slug) }}'.includes(searchPermission.toLowerCase())">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $permission->name }}</div>
                            <div class="text-[10px] font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ $permission->slug }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Modal 1: Create / Edit Custom Role Modal -->
    <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm" style="display: none;">
        <div class="bg-white dark:bg-slate-900 w-full max-w-2xl max-h-[90vh] rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden flex flex-col" @click.outside="showCreateModal = false">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/60">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-emerald-100 dark:bg-cyan-500/20 text-emerald-700 dark:text-cyan-300">
                        <i data-lucide="shield-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base" x-text="editingRole ? 'Edit Role Custom: ' + editingRole.name : 'Buat Role Custom Baru'"></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tentukan kombinasi hak akses yang diizinkan untuk peran ini.</p>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form :action="editingRole ? editFormAction : '{{ route('roles.store') }}'" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <template x-if="editingRole">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nama Role *</label>
                            <input type="text" name="name" required
                                   :value="editingRole ? editingRole.name : ''"
                                   placeholder="Contoh: Barista & Kasir Shift Pagi"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 rounded-xl text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-600 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi Peran</label>
                            <input type="text" name="description"
                                   :value="editingRole ? editingRole.description : ''"
                                   placeholder="Ringkasan tanggung jawab role ini..."
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 rounded-xl text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-600 transition">
                        </div>
                    </div>

                    <!-- Permissions Checkbox Group -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Pilih Hak Akses (Permissions) *</label>
                            <span class="text-xs font-mono text-emerald-600 dark:text-cyan-300 font-bold" x-text="selectedPermissions.length + ' hak akses dipilih'"></span>
                        </div>

                        <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                            @foreach($permissions as $category => $categoryPermissions)
                            @php
                                $categorySlugs = $categoryPermissions->pluck('slug')->toArray();
                            @endphp
                            <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-emerald-700 dark:text-cyan-300 text-xs font-bold uppercase tracking-wider">
                                        <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                        <span>{{ $category ?: 'Lainnya' }}</span>
                                    </div>
                                    <button type="button"
                                            @click="toggleCategory({{ Js::from($categorySlugs) }}, !isCategoryAllSelected({{ Js::from($categorySlugs) }}))"
                                            class="text-[11px] text-emerald-600 dark:text-cyan-400 hover:text-emerald-700 dark:hover:text-cyan-300 font-semibold">
                                        <span x-text="isCategoryAllSelected({{ Js::from($categorySlugs) }}) ? 'Batal Semua' : 'Pilih Semua'"></span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($categoryPermissions as $permission)
                                    <label class="flex items-start gap-2.5 p-2 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/50 hover:border-slate-300 dark:hover:border-slate-700 cursor-pointer transition text-xs select-none">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->slug }}"
                                               x-model="selectedPermissions"
                                               class="mt-0.5 rounded border-slate-300 dark:border-slate-800 text-emerald-600 focus:ring-emerald-500/20">
                                        <div>
                                            <span class="font-semibold text-slate-900 dark:text-white block">{{ $permission->name }}</span>
                                            <span class="text-[10px] font-mono text-slate-500">{{ $permission->slug }}</span>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-end gap-3">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm transition">
                        <span x-text="editingRole ? 'Simpan Perubahan' : 'Buat Role Custom'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Change Employee / Member Role Modal -->
    <div x-show="showMemberRoleModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm" style="display: none;">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md max-h-[90vh] overflow-y-auto rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden flex flex-col" @click.outside="showMemberRoleModal = false">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/60 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-emerald-100 dark:bg-cyan-500/20 text-emerald-700 dark:text-cyan-300">
                        <i data-lucide="user-cog" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Ubah Role Karyawan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="editingMember ? (editingMember.user ? editingMember.user.name : 'Anggota') : ''"></p>
                    </div>
                </div>
                <button type="button" @click="showMemberRoleModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form :action="memberRoleAction" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Role Baru *</label>
                    <select name="role_id" required x-model="memberSelectedRoleId" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 rounded-xl text-slate-900 dark:text-white text-xs transition">
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption->id }}">
                                {{ $roleOption->name }}{{ $roleOption->business_id ? ' (Custom)' : ' (Preset)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                    <p class="text-slate-800 dark:text-slate-300 font-semibold flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-emerald-600 dark:text-cyan-400"></i> Efek Perubahan Hak Akses:
                    </p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Perubahan role akan langsung berlaku seketika saat karyawan mengakses menu atau memproses transaksi di Cooca UMKM.
                    </p>
                </div>

                <!-- Modal Footer -->
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-end gap-2.5">
                    <button type="button" @click="showMemberRoleModal = false" class="px-4 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Perubahan Role</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
