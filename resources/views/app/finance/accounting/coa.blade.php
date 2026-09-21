@extends('layouts.app', ['title' => 'Bagan Akun (Chart of Accounts)'])

@section('content')
    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        deleteModalOpen: false,
        deleteAccountData: { id: '', code: '', name: '', url: '' },
        editAccount: { id: '', code: '', name: '', type: 'asset', normal_balance: 'debit', parent_id: '', is_active: true, is_system: false },
        openEdit(account) {
            this.editAccount = { ...account };
            this.editModalOpen = true;
        },
        confirmDelete(account, deleteUrl) {
            this.deleteAccountData = { id: account.id, code: account.code, name: account.name, url: deleteUrl };
            this.deleteModalOpen = true;
        }
    }" class="max-w-[1360px] mx-auto space-y-5 pb-16">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                    <span class="text-black/70 dark:text-white/70">Keuangan &amp; Akuntansi</span>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span class="text-black dark:text-white font-medium">Bagan Akun (COA)</span>
                </nav>
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Pohon Bagan Akun (Chart of Accounts)</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">Kelola struktur akun buku besar hierarkis standar SAK EMKM dan sub-akun kustom usaha</p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('finance.balance-sheet') }}"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center gap-1.5">
                    <i data-lucide="scale" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Neraca Keuangan</span>
                </a>
                <button type="button" @click="createModalOpen = true"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.35)]">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Sub-Akun</span>
                </button>
            </div>
        </header>

        {{-- ========================================================== --}}
        {{-- KPI STATS CARDS                                            --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Total Akun Aktif</p>
                    <p class="text-[22px] font-bold tabular-nums text-black dark:text-white mt-1">{{ $stats['active'] }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Dari {{ $stats['total'] }} akun terdaftar</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="book-marked" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Akun Sistem Inti</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6] mt-1">{{ $stats['system'] }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Terkunci &amp; Terlindungi</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Sub-Akun Kustom</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">{{ $stats['custom'] }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Dibuat oleh perusahaan</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="git-branch" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Integritas Jurnal</p>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            Standar SAK EMKM
                        </span>
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Double-entry konsisten</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="file-check-2" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- SEARCH & CATEGORY FILTER BAR                               --}}
        {{-- ========================================================== --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 space-y-3">
            <form method="GET" action="{{ route('finance.coa.index') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="relative flex-1 min-w-[220px]">
                    <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari kode atau nama akun..."
                        class="w-full h-9 pl-8 pr-3 text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                </div>

                <div class="flex items-center gap-1 overflow-x-auto py-0.5 text-[12px]">
                    <a href="{{ route('finance.coa.index', request()->except('type')) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ !request('type') ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Semua
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'asset'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'asset' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Aset
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'liability'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'liability' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Kewajiban
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'equity'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'equity' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Ekuitas
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'revenue'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'revenue' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Pendapatan
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'cogs'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'cogs' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        HPP
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'expense'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'expense' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Beban
                    </a>
                </div>

                <button type="submit"
                    class="h-9 px-4 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white text-[13px] font-semibold hover:bg-black/[0.1] transition-colors">
                    Filter
                </button>
            </form>
        </div>

        {{-- ========================================================== --}}
        {{-- ACCOUNTS TABLE                                             --}}
        {{-- ========================================================== --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="px-4 py-3.5 text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Kode Akun</th>
                            <th class="px-4 py-3.5 text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Nama Akun</th>
                            <th class="px-4 py-3.5 text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Kategori / Tipe</th>
                            <th class="px-4 py-3.5 text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Saldo Normal</th>
                            <th class="px-4 py-3.5 text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Status</th>
                            <th class="px-4 py-3.5 text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($accounts as $acc)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-3.5 tabular-nums text-[13.5px] sm:text-[14px] font-mono font-bold {{ $acc->is_system ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                                    @if($acc->parent_id)
                                        <span class="text-black/35 dark:text-white/35 mr-1 font-mono">↳</span>
                                    @endif
                                    {{ $acc->code }}
                                </td>
                                <td class="px-4 py-3.5 text-[13.5px] sm:text-[14px] font-medium text-black dark:text-white">
                                    {{ $acc->name }}
                                    @if($acc->parent)
                                        <span class="block text-[12px] text-black/45 dark:text-white/45 font-normal mt-0.5">Induk: {{ $acc->parent->code }} – {{ $acc->parent->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-[8px] text-[12px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/75">
                                        {{ $acc->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center uppercase px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wider {{ $acc->normal_balance === 'debit' ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#007AFF]/12 text-[#007AFF]' }}">
                                        {{ $acc->normal_balance }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        @if($acc->is_system)
                                            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-black/55 dark:text-white/55" title="Akun sistem tidak dapat dihapus">
                                                <i data-lucide="lock" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                                Sistem
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#34C759] dark:text-[#30D158]">
                                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                                Kustom
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('finance.general-ledger', ['account_id' => $acc->id]) }}"
                                            title="Buku Besar Akun Ini"
                                            class="w-9 h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#007AFF]/12 hover:text-[#007AFF] text-black/60 dark:text-white/60 flex items-center justify-center transition-colors">
                                            <i data-lucide="book-open" class="w-4 h-4"></i>
                                        </a>
                                        <button type="button"
                                            @click="openEdit({
                                                id: '{{ $acc->id }}',
                                                code: '{{ $acc->code }}',
                                                name: '{{ addslashes($acc->name) }}',
                                                type: '{{ $acc->type }}',
                                                normal_balance: '{{ $acc->normal_balance }}',
                                                parent_id: '{{ $acc->parent_id ?? '' }}',
                                                is_active: {{ $acc->is_active ? 'true' : 'false' }},
                                                is_system: {{ $acc->is_system ? 'true' : 'false' }}
                                            })"
                                            title="Ubah Akun"
                                            class="w-9 h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/60 dark:text-white/60 flex items-center justify-center transition-colors">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        @if($acc->canBeDeleted())
                                            <button type="button"
                                                @click="confirmDelete({ id: '{{ $acc->id }}', code: '{{ $acc->code }}', name: '{{ addslashes($acc->name) }}' }, '{{ route('finance.coa.destroy', $acc) }}')"
                                                title="Hapus Sub-Akun"
                                                class="w-9 h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#FF3B30]/15 hover:text-[#FF3B30] text-black/45 dark:text-white/45 flex items-center justify-center transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-black/40 dark:text-white/40">
                                    Tidak ada akun yang sesuai dengan kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- MODAL CREATE SUB-ACCOUNT                                   --}}
        {{-- ========================================================== --}}
        <div x-show="createModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            @keydown.escape.window="createModalOpen = false">
            <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[20px] shadow-2xl border border-black/10 dark:border-white/10 p-6 space-y-4"
                @click.outside="createModalOpen = false">
                <div class="flex items-center justify-between">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">Tambah Sub-Akun Baru</h2>
                    <button type="button" @click="createModalOpen = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('finance.coa.store') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Akun Induk (Parent COA)</label>
                        <select name="parent_id" class="w-full h-10 px-3 text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                            <option value="">-- Tanpa Induk (Akun Utama) --</option>
                            @foreach($parentCandidates as $p)
                                <option value="{{ $p->id }}">{{ $p->code }} – {{ $p->name }} ({{ $p->getTypeLabel() }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Kode Akun *</label>
                            <input type="text" name="code" required placeholder="Mis. 1-1001.01"
                                class="w-full h-10 px-3 text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tipe Akun *</label>
                            <select name="type" required class="w-full h-10 px-3 text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                                <option value="asset">Aset / Aktiva</option>
                                <option value="liability">Kewajiban / Hutang</option>
                                <option value="equity">Ekuitas / Modal</option>
                                <option value="revenue">Pendapatan</option>
                                <option value="cogs">HPP</option>
                                <option value="expense">Beban Operasional</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nama Sub-Akun *</label>
                        <input type="text" name="name" required placeholder="Mis. Kas Kasir Gerai Sudirman"
                            class="w-full h-10 px-3 text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Saldo Normal *</label>
                        <select name="normal_balance" required class="w-full h-10 px-3 text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                            <option value="debit">Debit</option>
                            <option value="credit">Kredit</option>
                        </select>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="createModalOpen = false" class="h-10 px-4 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">Batal</button>
                        <button type="submit" class="h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] shadow-md">Simpan Sub-Akun</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- MODAL EDIT ACCOUNT                                         --}}
        {{-- ========================================================== --}}
        <div x-show="editModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            @keydown.escape.window="editModalOpen = false">
            <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[20px] shadow-2xl border border-black/10 dark:border-white/10 p-6 space-y-4"
                @click.outside="editModalOpen = false">
                <div class="flex items-center justify-between">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">Ubah Akun</h2>
                    <button type="button" @click="editModalOpen = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" :action="'/finance/chart-of-accounts/' + editAccount.id" class="space-y-3.5">
                    @csrf
                    @method('PUT')

                    <template x-if="!editAccount.is_system">
                        <div>
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Kode Akun</label>
                            <input type="text" name="code" x-model="editAccount.code" required
                                class="w-full h-10 px-3 text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                        </div>
                    </template>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nama Akun</label>
                        <input type="text" name="name" x-model="editAccount.name" required
                            class="w-full h-10 px-3 text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-model="editAccount.is_active"
                            class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                        <label for="edit_is_active" class="text-[13px] text-black/80 dark:text-white/80">Akun Aktif</label>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="editModalOpen = false" class="h-10 px-4 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">Batal</button>
                        <button type="submit" class="h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] shadow-md">Perbarui Akun</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- MODAL DELETE CONFIRMATION (APPLE HIG BENTO MODAL SHEET)    --}}
        {{-- ========================================================== --}}
        <div x-show="deleteModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            @keydown.escape.window="deleteModalOpen = false">
            <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[22px] shadow-2xl border border-black/10 dark:border-white/10 p-6 space-y-5"
                @click.outside="deleteModalOpen = false">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-[14px] bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center shrink-0">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">Hapus Sub-Akun?</h2>
                        <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                            Apakah Anda yakin ingin menghapus sub-akun <span class="font-semibold text-black dark:text-white" x-text="deleteAccountData.code + ' – ' + deleteAccountData.name"></span>? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>

                <form method="POST" :action="deleteAccountData.url" class="flex items-center justify-end gap-2.5 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModalOpen = false"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-10 px-5 rounded-[12px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#D70015] active:scale-[0.98] transition-all shadow-[0_2px_8px_rgba(255,59,48,0.35)] flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>Hapus Sub-Akun</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
@endsection
