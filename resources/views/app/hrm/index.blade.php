@extends('layouts.app', [
    'title' => 'SDM & Penggajian (HRM)',
    'headerTitle' => 'Manajemen SDM & Penggajian',
    'headerSubtitle' => 'Kelola profil staf, struktur upah, kepesertaan BPJS, kasbon, dan jalankan penggajian bulanan otomatis.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-16" x-data="{
    activeTab: '{{ request('tab', $tab ?? 'employees') }}',
    showAddEmployeeModal: false,
    showEditEmployeeModal: false,
    showAddLoanModal: false,
    selectedEmployee: null,
    editFormAction: '',

    openEditEmployee(membership, updateUrl) {
        this.selectedEmployee = membership;
        this.editFormAction = updateUrl;
        this.showEditEmployeeModal = true;
    }
}">

    <!-- ======================================================== -->
    <!-- 1. BENTO EXECUTIVE STATS HERO                           -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Card 1: Total Staf -->
        <div class="p-4 sm:p-5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_2px_8px_rgba(0,0,0,0.02)] space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-medium uppercase tracking-wider">Total Staf Aktif</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[26px] font-bold text-black dark:text-white tracking-tight tabular-nums">
                {{ number_format($totalStaff) }} <span class="text-[14px] font-medium text-black/40 dark:text-white/40">Orang</span>
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45">Terdaftar dalam workspace bisnis</p>
        </div>

        <!-- Card 2: Estimasi Gaji Pokok -->
        <div class="p-4 sm:p-5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_2px_8px_rgba(0,0,0,0.02)] space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-medium uppercase tracking-wider">Total Gaji Pokok</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight tabular-nums truncate">
                Rp {{ number_format($totalBaseSalary, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45">Beban pokok bulanan reguler</p>
        </div>

        <!-- Card 3: Saldo Kasbon Aktif -->
        <div class="p-4 sm:p-5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_2px_8px_rgba(0,0,0,0.02)] space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-medium uppercase tracking-wider">Sisa Saldo Kasbon</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight tabular-nums truncate">
                Rp {{ number_format($totalActiveLoans, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45">Otomatis dipotong tiap payroll</p>
        </div>

        <!-- Card 4: Penggajian Terakhir -->
        <div class="p-4 sm:p-5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_2px_8px_rgba(0,0,0,0.02)] space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-medium uppercase tracking-wider">Penggajian Terakhir</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight truncate">
                {{ $latestPaidPayroll ? $latestPaidPayroll->formatted_period : 'Belum Ada' }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45">
                {{ $latestPaidPayroll ? 'Dibayar: Rp ' . number_format((float)$latestPaidPayroll->total_take_home_pay, 0, ',', '.') : 'Buat penggajian pertama' }}
            </p>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. SEGMENTED NAVIGATION CONTROLS                         -->
    <!-- ======================================================== -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/10 pb-2">
        <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] backdrop-blur-md">
            <button type="button" @click="activeTab = 'employees'"
                :class="activeTab === 'employees' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="px-4 py-2 rounded-[9px] text-[13px] transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="users" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Karyawan &amp; Profil Gaji</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/5 dark:bg-white/10">{{ $totalStaff }}</span>
            </button>

            <button type="button" @click="activeTab = 'payrolls'"
                :class="activeTab === 'payrolls' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="px-4 py-2 rounded-[9px] text-[13px] transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="receipt" class="w-4 h-4 text-[#34C759]"></i>
                <span>Penggajian Bulanan</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/5 dark:bg-white/10">{{ $payrolls->total() }}</span>
            </button>

            <button type="button" @click="activeTab = 'loans'"
                :class="activeTab === 'loans' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="px-4 py-2 rounded-[9px] text-[13px] transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="credit-card" class="w-4 h-4 text-[#FF9500]"></i>
                <span>Kasbon &amp; Pinjaman</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/5 dark:bg-white/10">{{ $loans->total() }}</span>
            </button>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <!-- Link ke Simulator Pajak & Kepatuhan -->
            <a href="{{ route('tax.index') }}"
                class="h-9 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] text-black/75 dark:text-white/75 text-[12px] font-medium flex items-center gap-1.5 transition active:scale-[0.97]">
                <i data-lucide="scale" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                <span>Simulator PPh 21 &amp; Pajak</span>
            </a>

            <!-- Action CTA Dinamis sesuai Tab -->
            <template x-if="activeTab === 'employees'">
                <button type="button" @click="showAddEmployeeModal = true"
                    class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] transition active:scale-[0.97] cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Karyawan</span>
                </button>
            </template>

            <template x-if="activeTab === 'payrolls'">
                <a href="{{ route('hrm.payrolls.create') }}"
                    class="h-9 px-3.5 rounded-[10px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)] transition active:scale-[0.97] cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Buat Penggajian Baru</span>
                </a>
            </template>

            <template x-if="activeTab === 'loans'">
                <button type="button" @click="showAddLoanModal = true"
                    class="h-9 px-3.5 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,149,0,0.25)] transition active:scale-[0.97] cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Catat Kasbon Baru</span>
                </button>
            </template>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: KARYAWAN & PROFIL GAJI                            -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'employees'" class="space-y-4" x-transition.opacity>
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-2xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.02] text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                            <th class="py-3.5 px-4 sm:px-5">Karyawan</th>
                            <th class="py-3.5 px-3">Jabatan &amp; Peran</th>
                            <th class="py-3.5 px-3">Tipe Kerja</th>
                            <th class="py-3.5 px-3">Struktur Upah</th>
                            <th class="py-3.5 px-3">BPJS &amp; PTKP</th>
                            <th class="py-3.5 px-3">Rekening Bank</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($memberships as $m)
                            @php
                                $u = $m->user;
                                $isOwner = ($m->role === 'owner');
                            @endphp
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <!-- Karyawan -->
                                <td class="py-3.5 px-4 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-[#007AFF]/12 text-[#007AFF] font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ substr($u->name ?? 'K', 0, 2) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-black dark:text-white truncate">
                                                {{ $u->name ?? 'User' }}
                                                @if($isOwner)
                                                    <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full bg-[#FF9500]/15 text-[#FF9500] font-bold">Owner</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-black/50 dark:text-white/50 truncate">
                                                {{ $u->email ?? '-' }}
                                            </div>
                                            @if($m->whatsapp_number)
                                                <div class="text-[11px] text-[#25D366] flex items-center gap-1 mt-0.5">
                                                    <i data-lucide="phone" class="w-3 h-3"></i>
                                                    <span>{{ $m->whatsapp_number }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Jabatan & Peran -->
                                <td class="py-3.5 px-3">
                                    <div class="font-medium text-black dark:text-white">{{ $m->job_title ?: ucfirst($m->role) }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">{{ $m->customRole?->name ?? ucfirst($m->role) }}</div>
                                </td>

                                <!-- Tipe Kerja -->
                                <td class="py-3.5 px-3">
                                    @if($m->employment_type === 'daily_worker')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#FF9500]">
                                            Pekerja Harian
                                        </span>
                                    @elseif($m->employment_type === 'contract')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#AF52DE]/12 text-[#AF52DE]">
                                            Kontrak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#34C759]">
                                            Tetap
                                        </span>
                                    @endif
                                    @if($m->join_date)
                                        <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">
                                            Masuk: {{ \Carbon\Carbon::parse($m->join_date)->translatedFormat('d M Y') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Struktur Upah -->
                                <td class="py-3.5 px-3">
                                    @if($m->employment_type === 'daily_worker')
                                        <div class="font-semibold text-black dark:text-white tabular-nums">
                                            Rp {{ number_format((float)$m->daily_rate, 0, ',', '.') }} <span class="text-[10px] font-normal text-black/50 dark:text-white/50">/hari</span>
                                        </div>
                                    @else
                                        <div class="font-semibold text-black dark:text-white tabular-nums">
                                            Rp {{ number_format((float)$m->base_salary, 0, ',', '.') }}
                                        </div>
                                        @if($m->fixed_allowances > 0 || $m->variable_allowances > 0)
                                            <div class="text-[11px] text-black/50 dark:text-white/50 tabular-nums">
                                                + Tunj: Rp {{ number_format((float)($m->fixed_allowances + $m->variable_allowances), 0, ',', '.') }}
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <!-- BPJS & PTKP -->
                                <td class="py-3.5 px-3">
                                    <div class="flex flex-wrap gap-1 items-center">
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">
                                            {{ $m->tax_ptkp_status ?: 'TK/0' }}
                                        </span>
                                        @if($m->bpjs_tk_enabled)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">
                                                BPJS TK
                                            </span>
                                        @endif
                                        @if($m->bpjs_kes_enabled)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#34C759]/12 text-[#34C759]">
                                                BPJS Kes
                                            </span>
                                        @endif
                                        @if(! $m->bpjs_tk_enabled && ! $m->bpjs_kes_enabled)
                                            <span class="text-[11px] text-black/35 dark:text-white/35 italic">Non-BPJS</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Rekening Bank -->
                                <td class="py-3.5 px-3">
                                    @if($m->bank_account_number)
                                        <div class="font-medium text-black dark:text-white">{{ $m->bank_name ?: 'Bank' }}</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50 tabular-nums">{{ $m->bank_account_number }}</div>
                                    @else
                                        <span class="text-[11px] text-black/35 dark:text-white/35">Tunai</span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                            @click="openEditEmployee({{ Js::from($m) }}, '{{ route('hrm.employees.update', $m->id) }}')"
                                            class="h-7.5 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1 cursor-pointer">
                                            <i data-lucide="edit-3" class="w-3 h-3"></i>
                                            <span>Edit Profil</span>
                                        </button>

                                        @if(! $isOwner)
                                            <form method="POST" action="{{ route('hrm.employees.destroy', $m->id) }}"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus staf ini dari workspace?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="h-7.5 w-7.5 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] transition-all flex items-center justify-center cursor-pointer"
                                                    title="Hapus Staf">
                                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40">
                                    Belum ada staf yang terdaftar. Klik "+ Tambah Karyawan" untuk menambahkan staf baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: PENGGAJIAN BULANAN (PAYROLL RUNS)                -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'payrolls'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-2xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.02] text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                            <th class="py-3.5 px-4 sm:px-5">Periode Penggajian</th>
                            <th class="py-3.5 px-3">Staf Tercakup</th>
                            <th class="py-3.5 px-3">Gaji Kotor (Bruto)</th>
                            <th class="py-3.5 px-3">Total Potongan</th>
                            <th class="py-3.5 px-3">Gaji Bersih (THP)</th>
                            <th class="py-3.5 px-3">Total Beban Usaha</th>
                            <th class="py-3.5 px-3">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($payrolls as $p)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3.5 px-4 sm:px-5 font-semibold text-black dark:text-white">
                                    <a href="{{ route('hrm.payrolls.show', $p->id) }}" class="hover:text-[#007AFF] transition">
                                        {{ $p->title }}
                                    </a>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 font-normal">
                                        Dibuat {{ $p->created_at->translatedFormat('d M Y') }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-medium text-black dark:text-white">
                                    {{ $p->total_employees_count }} Orang
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black dark:text-white">
                                    Rp {{ number_format((float)$p->total_gross_pay, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-[#FF3B30]">
                                    -Rp {{ number_format((float)$p->total_deductions, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-bold text-[#34C759]">
                                    Rp {{ number_format((float)$p->total_take_home_pay, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-semibold text-black dark:text-white">
                                    Rp {{ number_format((float)$p->total_company_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($p->status === 'paid')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                            <span>Dibayar</span>
                                        </span>
                                    @elseif($p->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]">
                                            <i data-lucide="shield-check" class="w-3 h-3"></i>
                                            <span>Disetujui</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <span>Draft</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('hrm.payrolls.show', $p->id) }}"
                                            class="h-7.5 px-3 rounded-[8px] text-[11px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center gap-1">
                                            <span>Detail &amp; Slip</span>
                                            <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                        </a>

                                        @if($p->status === 'draft')
                                            <form method="POST" action="{{ route('hrm.payrolls.destroy', $p->id) }}"
                                                onsubmit="return confirm('Hapus draf penggajian ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="h-7.5 w-7.5 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] transition-all flex items-center justify-center cursor-pointer"
                                                    title="Hapus Draft">
                                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                                    Belum ada rekam jejak penggajian bulanan. Klik "+ Buat Penggajian Baru" untuk memulai perhitungan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payrolls->hasPages())
                <div class="p-3 border-t border-black/5 dark:border-white/5">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: KASBON & PINJAMAN KARYAWAN                        -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'loans'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-2xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.02] text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                            <th class="py-3.5 px-4 sm:px-5">No. Pinjaman</th>
                            <th class="py-3.5 px-3">Karyawan</th>
                            <th class="py-3.5 px-3">Tanggal</th>
                            <th class="py-3.5 px-3">Plafon Pokok</th>
                            <th class="py-3.5 px-3">Tenor</th>
                            <th class="py-3.5 px-3">Cicilan / Bulan</th>
                            <th class="py-3.5 px-3">Sisa Saldo</th>
                            <th class="py-3.5 px-3">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($loans as $loan)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3.5 px-4 sm:px-5 font-semibold text-black dark:text-white tabular-nums">
                                    {{ $loan->loan_number }}
                                    @if($loan->purpose)
                                        <div class="text-[11px] text-black/45 dark:text-white/45 font-normal">{{ $loan->purpose }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 font-medium text-black dark:text-white">
                                    {{ $loan->user?->name ?? 'Karyawan' }}
                                </td>
                                <td class="py-3.5 px-3 text-black/60 dark:text-white/60 tabular-nums">
                                    {{ \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-semibold text-black dark:text-white">
                                    Rp {{ number_format((float)$loan->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black/70 dark:text-white/70">
                                    {{ $loan->tenor_months }} Bulan
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-[#FF9500] font-medium">
                                    Rp {{ number_format((float)$loan->monthly_installment, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-bold {{ $loan->remaining_balance > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }}">
                                    Rp {{ number_format((float)$loan->remaining_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($loan->status === 'completed' || $loan->remaining_balance <= 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            Lunas
                                        </span>
                                    @elseif($loan->status === 'cancelled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60">
                                            Dibatalkan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            Berjalan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($loan->status === 'active' && $loan->remaining_balance > 0)
                                        <form method="POST" action="{{ route('hrm.loans.cancel', $loan->id) }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan sisa pinjaman ini?')">
                                            @csrf
                                            <button type="submit"
                                                class="h-7 px-2.5 rounded-[7px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] transition-all cursor-pointer">
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-black/35 dark:text-white/35">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-black/40 dark:text-white/40">
                                    Belum ada catatan kasbon atau pinjaman karyawan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($loans->hasPages())
                <div class="p-3 border-t border-black/5 dark:border-white/5">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: TAMBAH KARYAWAN BARU LENGKAP                    -->
    <!-- ======================================================== -->
    <div x-show="showAddEmployeeModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;">
        <div @click.outside="showAddEmployeeModal = false" class="w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.2)] overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">Tambah Karyawan Baru</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Isi data akun kredensial dan struktur upah karyawan.</p>
                </div>
                <button type="button" @click="showAddEmployeeModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.employees.store') }}" class="p-5 overflow-y-auto space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nama Lengkap <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Rian Anggara"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Email Login <span class="text-[#FF3B30]">*</span></label>
                        <input type="email" name="email" required placeholder="rian@tokobisnis.id"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Peran Akses (Role) <span class="text-[#FF3B30]">*</span></label>
                        <select name="role_id" required class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            @foreach($availableRoles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Jabatan Spesifik</label>
                        <input type="text" name="job_title" placeholder="Contoh: Barista Lead"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tipe Kerja <span class="text-[#FF3B30]">*</span></label>
                        <select name="employment_type" required class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="permanent">Karyawan Tetap</option>
                            <option value="contract">Kontrak</option>
                            <option value="daily_worker">Pekerja Harian</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Gaji Pokok (Rp)</label>
                        <input type="number" name="base_salary" step="1000" placeholder="5000000"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Upah Harian (Rp)</label>
                        <input type="number" name="daily_rate" step="1000" placeholder="150000"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Bergabung</label>
                        <input type="date" name="join_date" value="{{ date('Y-m-d') }}"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tunjangan Tetap Bulanan (Rp)</label>
                        <input type="number" name="fixed_allowances" step="1000" placeholder="Contoh: Jabatan 1000000"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tunjangan Variabel / Makan (Rp)</label>
                        <input type="number" name="variable_allowances" step="1000" placeholder="Contoh: Transport 500000"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <!-- Kepatuhan Pajak & BPJS -->
                <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Kepatuhan Pajak &amp; BPJS RI</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Status PTKP (PPh 21 TER)</label>
                            <select name="tax_ptkp_status" class="w-full h-9 px-2.5 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white">
                                <option value="TK/0">TK/0 (Lajang 0 Tanggungan)</option>
                                <option value="TK/1">TK/1 (Lajang 1 Tanggungan)</option>
                                <option value="TK/2">TK/2 (Lajang 2 Tanggungan)</option>
                                <option value="TK/3">TK/3 (Lajang 3 Tanggungan)</option>
                                <option value="K/0">K/0 (Menikah 0 Tanggungan)</option>
                                <option value="K/1">K/1 (Menikah 1 Tanggungan)</option>
                                <option value="K/2">K/2 (Menikah 2 Tanggungan)</option>
                                <option value="K/3">K/3 (Menikah 3 Tanggungan)</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pt-5">
                            <input type="checkbox" id="add_bpjs_tk" name="bpjs_tk_enabled" value="1" checked class="w-4 h-4 rounded text-[#007AFF] focus:ring-0">
                            <label for="add_bpjs_tk" class="text-[12px] font-medium text-black/80 dark:text-white/80">BPJS Ketenagakerjaan</label>
                        </div>
                        <div class="flex items-center gap-2 pt-5">
                            <input type="checkbox" id="add_bpjs_kes" name="bpjs_kes_enabled" value="1" checked class="w-4 h-4 rounded text-[#007AFF] focus:ring-0">
                            <label for="add_bpjs_kes" class="text-[12px] font-medium text-black/80 dark:text-white/80">BPJS Kesehatan</label>
                        </div>
                    </div>
                </div>

                <!-- Rekening Bank & WhatsApp -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" placeholder="BCA / Mandiri / BRI"
                            class="w-full h-9 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Nomor Rekening</label>
                        <input type="text" name="bank_account_number" placeholder="Contoh: 827192819"
                            class="w-full h-9 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Nomor WhatsApp</label>
                        <input type="text" name="whatsapp_number" placeholder="0812xxxxxxxx"
                            class="w-full h-9 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white">
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddEmployeeModal = false"
                        class="h-9 px-4 rounded-[9px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-9 px-4 rounded-[9px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition active:scale-[0.97] cursor-pointer">
                        Simpan Karyawan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 2: EDIT PROFIL HRM KARYAWAN                        -->
    <!-- ======================================================== -->
    <div x-show="showEditEmployeeModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;">
        <div @click.outside="showEditEmployeeModal = false" class="w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.2)] overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">Edit Profil HRM Karyawan</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Perbarui struktur gaji pokok, tunjangan, dan status BPJS.</p>
                </div>
                <button type="button" @click="showEditEmployeeModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" :action="editFormAction" class="p-5 overflow-y-auto space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nama Lengkap <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" required :value="selectedEmployee?.user?.name || ''"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Jabatan Spesifik</label>
                        <input type="text" name="job_title" :value="selectedEmployee?.job_title || ''" placeholder="Contoh: Barista Lead"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Peran Akses (Role) <span class="text-[#FF3B30]">*</span></label>
                        <select name="role_id" required class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            @foreach($availableRoles as $r)
                                <option value="{{ $r->id }}" :selected="selectedEmployee?.role_id === '{{ $r->id }}'">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tipe Kerja <span class="text-[#FF3B30]">*</span></label>
                        <select name="employment_type" required class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="permanent" :selected="selectedEmployee?.employment_type === 'permanent'">Karyawan Tetap</option>
                            <option value="contract" :selected="selectedEmployee?.employment_type === 'contract'">Kontrak</option>
                            <option value="daily_worker" :selected="selectedEmployee?.employment_type === 'daily_worker'">Pekerja Harian</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Gaji Pokok (Rp)</label>
                        <input type="number" name="base_salary" step="1000" :value="selectedEmployee?.base_salary || 0"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Upah Harian (Rp)</label>
                        <input type="number" name="daily_rate" step="1000" :value="selectedEmployee?.daily_rate || 0"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Bergabung</label>
                        <input type="date" name="join_date" :value="selectedEmployee?.join_date ? selectedEmployee.join_date.substring(0, 10) : ''"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tunjangan Tetap (Rp)</label>
                        <input type="number" name="fixed_allowances" step="1000" :value="selectedEmployee?.fixed_allowances || 0"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tunjangan Tambahan / Makan (Rp)</label>
                        <input type="number" name="variable_allowances" step="1000" :value="selectedEmployee?.variable_allowances || 0"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <!-- Kepatuhan Pajak & BPJS -->
                <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Kepatuhan Pajak &amp; BPJS RI</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Status PTKP</label>
                            <select name="tax_ptkp_status" class="w-full h-9 px-2.5 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white">
                                <option value="TK/0" :selected="selectedEmployee?.tax_ptkp_status === 'TK/0'">TK/0</option>
                                <option value="TK/1" :selected="selectedEmployee?.tax_ptkp_status === 'TK/1'">TK/1</option>
                                <option value="TK/2" :selected="selectedEmployee?.tax_ptkp_status === 'TK/2'">TK/2</option>
                                <option value="TK/3" :selected="selectedEmployee?.tax_ptkp_status === 'TK/3'">TK/3</option>
                                <option value="K/0" :selected="selectedEmployee?.tax_ptkp_status === 'K/0'">K/0</option>
                                <option value="K/1" :selected="selectedEmployee?.tax_ptkp_status === 'K/1'">K/1</option>
                                <option value="K/2" :selected="selectedEmployee?.tax_ptkp_status === 'K/2'">K/2</option>
                                <option value="K/3" :selected="selectedEmployee?.tax_ptkp_status === 'K/3'">K/3</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pt-5">
                            <input type="checkbox" id="edit_bpjs_tk" name="bpjs_tk_enabled" value="1" :checked="selectedEmployee?.bpjs_tk_enabled" class="w-4 h-4 rounded text-[#007AFF] focus:ring-0">
                            <label for="edit_bpjs_tk" class="text-[12px] font-medium text-black/80 dark:text-white/80">BPJS Ketenagakerjaan</label>
                        </div>
                        <div class="flex items-center gap-2 pt-5">
                            <input type="checkbox" id="edit_bpjs_kes" name="bpjs_kes_enabled" value="1" :checked="selectedEmployee?.bpjs_kes_enabled" class="w-4 h-4 rounded text-[#007AFF] focus:ring-0">
                            <label for="edit_bpjs_kes" class="text-[12px] font-medium text-black/80 dark:text-white/80">BPJS Kesehatan</label>
                        </div>
                    </div>
                </div>

                <!-- Rekening Bank & WhatsApp -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" :value="selectedEmployee?.bank_name || ''" placeholder="BCA / Mandiri"
                            class="w-full h-9 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Nomor Rekening</label>
                        <input type="text" name="bank_account_number" :value="selectedEmployee?.bank_account_number || ''" placeholder="827192819"
                            class="w-full h-9 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Nomor WhatsApp</label>
                        <input type="text" name="whatsapp_number" :value="selectedEmployee?.whatsapp_number || ''" placeholder="0812xxxxxxxx"
                            class="w-full h-9 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white">
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showEditEmployeeModal = false"
                        class="h-9 px-4 rounded-[9px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-9 px-4 rounded-[9px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition active:scale-[0.97] cursor-pointer">
                        Perbarui Profil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 3: CATAT KASBON / PINJAMAN BARU                   -->
    <!-- ======================================================== -->
    <div x-show="showAddLoanModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;">
        <div @click.outside="showAddLoanModal = false" class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.2)] overflow-hidden">
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">Catat Kasbon / Pinjaman</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Cicilan akan otomatis memotong penggajian tiap bulan.</p>
                </div>
                <button type="button" @click="showAddLoanModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.loans.store') }}" class="p-5 space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Pilih Karyawan <span class="text-[#FF3B30]">*</span></label>
                    <select name="user_id" required class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($memberships as $m)
                            <option value="{{ $m->user_id }}">{{ $m->user?->name ?? 'User' }} ({{ $m->job_title ?: ucfirst($m->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nominal Pinjaman (Rp) <span class="text-[#FF3B30]">*</span></label>
                    <input type="number" name="amount" required step="10000" min="10000" placeholder="Contoh: 1000000"
                        class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tenor (Bulan) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="tenor_months" required min="1" max="60" value="1"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Kasbon</label>
                        <input type="date" name="loan_date" required value="{{ date('Y-m-d') }}"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tujuan / Keperluan</label>
                    <input type="text" name="purpose" placeholder="Contoh: Kebutuhan darurat / sewa rumah"
                        class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddLoanModal = false"
                        class="h-9 px-4 rounded-[9px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-9 px-4 rounded-[9px] text-[12px] font-semibold text-white bg-[#FF9500] hover:bg-[#E08500] transition active:scale-[0.97] cursor-pointer">
                        Simpan Kasbon
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
