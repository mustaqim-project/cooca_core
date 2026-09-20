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
    showAddCorrectionModal: false,
    showReviewCorrectionModal: false,
    selectedEmployee: null,
    selectedCorrection: null,
    editFormAction: '',

    // Geolocation & Live Attendance State
    gpsStatus: 'idle',
    gpsMessage: 'Menunggu deteksi sinyal GPS...',
    currentLat: null,
    currentLng: null,
    currentAccuracy: null,
    distanceMeters: null,
    officeLat: {{ $primaryLocation && $primaryLocation->latitude ? (float) $primaryLocation->latitude : 'null' }},
    officeLng: {{ $primaryLocation && $primaryLocation->longitude ? (float) $primaryLocation->longitude : 'null' }},
    officeRadius: {{ $primaryLocation && $primaryLocation->geofence_radius_meters ? (int) $primaryLocation->geofence_radius_meters : 50 }},
    isFreeLocation: {{ ($currentUserMembership && $currentUserMembership->isFreeLocation()) ? 'true' : 'false' }},
    liveClock: '',
    liveSeconds: '',

    init() {
        this.updateClock();
        setInterval(() => this.updateClock(), 1000);
        this.requestLocation();
    },

    updateClock() {
        const now = new Date();
        this.liveClock = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        this.liveSeconds = String(now.getSeconds()).padStart(2, '0');
    },

    requestLocation() {
        if (!navigator.geolocation) {
            this.gpsStatus = 'error';
            this.gpsMessage = 'Browser Anda tidak mendukung pendeteksi lokasi GPS.';
            return;
        }

        this.gpsStatus = 'locating';
        this.gpsMessage = 'Mendeteksi koordinat GPS presisi tinggi...';

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.currentLat = pos.coords.latitude;
                this.currentLng = pos.coords.longitude;
                this.currentAccuracy = Math.round(pos.coords.accuracy);

                if (this.currentAccuracy > 100) {
                    this.gpsStatus = 'error';
                    this.gpsMessage = 'Akurasi GPS rendah (' + this.currentAccuracy + 'm > 100m). Mohon aktifkan GPS akurasi tinggi.';
                    return;
                }

                if (this.officeLat && this.officeLng) {
                    this.distanceMeters = this.calcHaversine(this.currentLat, this.currentLng, this.officeLat, this.officeLng);
                }

                this.gpsStatus = 'ready';
                if (this.isFreeLocation) {
                    this.gpsMessage = 'Mode Bebas Lokasi aktif (Akurasi: ' + this.currentAccuracy + 'm)';
                } else if (this.distanceMeters !== null) {
                    if (this.distanceMeters <= this.officeRadius) {
                        this.gpsMessage = 'Dalam Radius Kantor (' + this.distanceMeters + 'm dari batas ' + this.officeRadius + 'm)';
                    } else {
                        this.gpsMessage = 'Di Luar Radius Kantor (' + this.distanceMeters + 'm > ' + this.officeRadius + 'm)';
                    }
                } else {
                    this.gpsMessage = 'GPS Terkunci (Akurasi: ' + this.currentAccuracy + 'm)';
                }
            },
            (err) => {
                this.gpsStatus = 'denied';
                if (err.code === 1) {
                    this.gpsMessage = 'Akses lokasi ditolak. Mohon aktifkan izin GPS di peramban Anda.';
                } else {
                    this.gpsMessage = 'Gagal mendeteksi lokasi GPS: ' + err.message;
                }
            },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 10000 }
        );
    },

    calcHaversine(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    },

    openReviewCorrection(item) {
        this.selectedCorrection = item;
        this.showReviewCorrectionModal = true;
    },

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
        <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] backdrop-blur-md overflow-x-auto max-w-full">
            <button type="button" @click="activeTab = 'employees'"
                :class="activeTab === 'employees' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="px-3.5 py-2 rounded-[9px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap">
                <i data-lucide="users" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Karyawan &amp; Profil Gaji</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/5 dark:bg-white/10">{{ $totalStaff }}</span>
            </button>

            <button type="button" @click="activeTab = 'attendance'"
                :class="activeTab === 'attendance' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="px-3.5 py-2 rounded-[9px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap">
                <i data-lucide="clock" class="w-4 h-4 text-[#34C759]"></i>
                <span>Presensi Harian</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-[#34C759]/10 text-[#34C759] font-semibold">{{ $todayPresentCount }}</span>
            </button>

            <button type="button" @click="activeTab = 'corrections'"
                :class="activeTab === 'corrections' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="px-3.5 py-2 rounded-[9px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap">
                <i data-lucide="file-check-2" class="w-4 h-4 text-[#FF9500]"></i>
                <span>Tiket Koreksi</span>
                @if($pendingCorrectionsCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-[#FF3B30] text-white font-bold animate-pulse">{{ $pendingCorrectionsCount }}</span>
                @else
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/5 dark:bg-white/10">{{ $corrections->total() }}</span>
                @endif
            </button>

            <button type="button" @click="activeTab = 'payrolls'"
                :class="activeTab === 'payrolls' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="px-3.5 py-2 rounded-[9px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap">
                <i data-lucide="receipt" class="w-4 h-4 text-[#AF52DE]"></i>
                <span>Penggajian Bulanan</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/5 dark:bg-white/10">{{ $payrolls->total() }}</span>
            </button>

            <button type="button" @click="activeTab = 'loans'"
                :class="activeTab === 'loans' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="px-3.5 py-2 rounded-[9px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap">
                <i data-lucide="credit-card" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Kasbon &amp; Pinjaman</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/5 dark:bg-white/10">{{ $loans->total() }}</span>
            </button>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <!-- Link ke Simulator Pajak & Kepatuhan -->
            <a href="{{ route('tax.index') }}"
                class="h-9 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] text-black/75 dark:text-white/75 text-[12px] font-medium flex items-center gap-1.5 transition active:scale-[0.97]">
                <i data-lucide="scale" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                <span>Simulator PPh 21</span>
            </a>

            <!-- Action CTA Dinamis sesuai Tab -->
            <template x-if="activeTab === 'employees'">
                <button type="button" @click="showAddEmployeeModal = true"
                    class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] transition active:scale-[0.97] cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Karyawan</span>
                </button>
            </template>

            <template x-if="activeTab === 'attendance'">
                <button type="button" @click="requestLocation()"
                    class="h-9 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] text-black/75 dark:text-white/75 text-[12px] font-medium flex items-center gap-1.5 transition active:scale-[0.97] cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Perbarui Lokasi GPS</span>
                </button>
            </template>

            <template x-if="activeTab === 'corrections'">
                <button type="button" @click="showAddCorrectionModal = true"
                    class="h-9 px-3.5 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,149,0,0.25)] transition active:scale-[0.97] cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Ajukan Tiket Koreksi</span>
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
    <!-- TAB 4: PRESENSI & ABSENSI HARIAN (PRD-06)                 -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'attendance'" class="space-y-6" x-transition.opacity style="display: none;">
        <!-- 1. BENTO HERO: WIDGET PRESENSI MANDIRI & GEOFENCING -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)] space-y-5">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white">Presensi Mandiri Hari Ini</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} • Waktu Indonesia Barat (WIB)
                        </p>
                    </div>
                </div>

                <!-- Digital Clock Display -->
                <div class="flex items-baseline gap-1 px-4 py-2 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10">
                    <span class="text-[28px] sm:text-[34px] font-extrabold tracking-tight tabular-nums text-black dark:text-white" x-text="liveClock">00:00</span>
                    <span class="text-[16px] sm:text-[18px] font-bold text-black/40 dark:text-white/40 tabular-nums" x-text="':' + liveSeconds">:00</span>
                    <span class="ml-1 text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase">WIB</span>
                </div>
            </div>

            <!-- Geolocation Radar Status & Policy Indicator -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                <!-- Location Status Card -->
                <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5 md:col-span-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-[#007AFF]"></i>
                            <span class="text-[12px] font-semibold text-black/70 dark:text-white/70">Status Lokasi &amp; Geofencing</span>
                        </div>
                        <template x-if="gpsStatus === 'ready'">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                :class="isFreeLocation || (distanceMeters !== null && distanceMeters <= officeRadius) ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF3B30]/15 text-[#FF3B30]'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="isFreeLocation || (distanceMeters !== null && distanceMeters <= officeRadius) ? 'bg-[#34C759]' : 'bg-[#FF3B30]'"></span>
                                <span x-text="isFreeLocation ? 'Bebas Lokasi' : (distanceMeters !== null && distanceMeters <= officeRadius ? 'Dalam Jangkauan' : 'Di Luar Radius')"></span>
                            </span>
                        </template>
                        <template x-if="gpsStatus === 'locating'">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#007AFF]/15 text-[#007AFF]">
                                <i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i>
                                <span>Mencari GPS...</span>
                            </span>
                        </template>
                        <template x-if="gpsStatus === 'error' || gpsStatus === 'denied'">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/15 text-[#FF3B30]">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                <span>GPS Nonaktif</span>
                            </span>
                        </template>
                    </div>

                    <p class="text-[12.5px] font-medium text-black/80 dark:text-white/80" x-text="gpsMessage">
                        Menghubungkan ke sensor GPS perangkat...
                    </p>

                    <div class="text-[11px] text-black/45 dark:text-white/45 flex flex-wrap items-center gap-x-3 gap-y-1">
                        @if($currentUserMembership && $currentUserMembership->isFreeLocation())
                            <span>Kebijakan: <strong class="text-[#007AFF]">Mode Bebas Lokasi</strong> (Sales / Kurir / Lapangan)</span>
                        @else
                            <span>Kantor: <strong>{{ $primaryLocation?->name ?? 'Outlet Utama' }}</strong></span>
                            <span>Batas Radius: <strong>{{ $primaryLocation?->geofence_radius_meters ?? 50 }} meter</strong></span>
                        @endif
                        <template x-if="currentAccuracy">
                            <span>Akurasi: <span class="tabular-nums" x-text="currentAccuracy + 'm'"></span></span>
                        </template>
                    </div>
                </div>

                <!-- GPS Refresh Trigger Card -->
                <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex flex-col justify-between gap-2">
                    <span class="text-[11px] text-black/50 dark:text-white/50 uppercase font-semibold tracking-wider">Pemeriksaan Sensor</span>
                    <button type="button" @click="requestLocation()"
                        class="w-full h-9 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Deteksi Ulang GPS</span>
                    </button>
                </div>
            </div>

            <!-- Action Controls Form -->
            <div class="pt-2">
                @if(!$currentUserAttendance || !$currentUserAttendance->clock_in_at)
                    <!-- FORM CLOCK-IN MASUK -->
                    <form method="POST" action="{{ route('hrm.attendance.clock-in') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="latitude" :value="currentLat">
                        <input type="hidden" name="longitude" :value="currentLng">
                        <input type="hidden" name="accuracy" :value="currentAccuracy">
                        <input type="hidden" name="location_id" value="{{ $primaryLocation?->id }}">

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <input type="text" name="notes" placeholder="Catatan kehadiran (opsional, misal: Dinas luar, standby shift pagi)"
                                class="flex-1 h-12 px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#34C759]/50 placeholder:text-black/35 dark:placeholder:text-white/35">

                            <button type="submit"
                                class="h-12 px-6 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13.5px] font-bold flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(52,199,89,0.35)] transition active:scale-[0.98] cursor-pointer shrink-0">
                                <i data-lucide="log-in" class="w-5 h-5"></i>
                                <span>Clock-In Masuk</span>
                            </button>
                        </div>
                    </form>
                @elseif(!$currentUserAttendance->clock_out_at)
                    <!-- FORM CLOCK-OUT PULANG -->
                    <div class="p-4 rounded-[14px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/20 text-[#34C759]">
                                    Sedang Bekerja
                                </span>
                                <span class="text-[13px] font-semibold text-black dark:text-white">
                                    Clock-In: {{ $currentUserAttendance->clock_in_at->format('H:i') }} WIB
                                </span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60">
                                Status Masuk: <strong class="{{ $currentUserAttendance->isLate() ? 'text-[#FF9500]' : 'text-[#34C759]' }}">{{ $currentUserAttendance->isLate() ? 'Terlambat (' . $currentUserAttendance->late_minutes . ' menit)' : 'Tepat Waktu' }}</strong>
                                @if($currentUserAttendance->clock_in_distance_meters)
                                    • Jarak: {{ $currentUserAttendance->clock_in_distance_meters }}m dari kantor
                                @endif
                            </p>
                        </div>

                        <form method="POST" action="{{ route('hrm.attendance.clock-out') }}" class="w-full sm:w-auto">
                            @csrf
                            <input type="hidden" name="latitude" :value="currentLat">
                            <input type="hidden" name="longitude" :value="currentLng">
                            <input type="hidden" name="accuracy" :value="currentAccuracy">

                            <button type="submit"
                                class="w-full sm:w-auto h-11 px-5 rounded-[11px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_4px_14px_rgba(0,122,255,0.3)] transition active:scale-[0.98] cursor-pointer">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                <span>Clock-Out Pulang</span>
                            </button>
                        </form>
                    </div>
                @else
                    <!-- SELESAI HARI INI -->
                    <div class="p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#34C759]/20 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="check-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-[13px] font-bold text-black dark:text-white">Hari Kerja Selesai Lengkap</span>
                                <p class="text-[11.5px] text-black/60 dark:text-white/60">
                                    Masuk: <strong>{{ $currentUserAttendance->clock_in_at?->format('H:i') }}</strong> •
                                    Pulang: <strong>{{ $currentUserAttendance->clock_out_at?->format('H:i') }}</strong> •
                                    Durasi Kerja: <strong>{{ $currentUserAttendance->formatted_work_duration }}</strong>
                                    @if($currentUserAttendance->overtime_minutes > 0)
                                        (<span class="text-[#007AFF] font-bold">+{{ $currentUserAttendance->overtime_minutes }}m Lembur</span>)
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759] text-white">
                            Tuntas
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. REKAP KPI ATTENDANCE HARI INI -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase">Hadir Hari Ini</span>
                <div class="text-[22px] font-bold text-black dark:text-white tabular-nums">{{ $todayPresentCount }}</div>
                <p class="text-[11px] text-[#34C759] font-medium">Staf tercatat di sistem</p>
            </div>
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase">Terlambat</span>
                <div class="text-[22px] font-bold text-[#FF9500] tabular-nums">{{ $todayLateCount }}</div>
                <p class="text-[11px] text-black/45 dark:text-white/45">Lewat jam masuk standar</p>
            </div>
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase">Bebas Lokasi</span>
                <div class="text-[22px] font-bold text-[#007AFF] tabular-nums">{{ $todayFreeCount }}</div>
                <p class="text-[11px] text-black/45 dark:text-white/45">Sales &amp; staf lapangan</p>
            </div>
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase">Koreksi Menunggu</span>
                <div class="text-[22px] font-bold {{ $pendingCorrectionsCount > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} tabular-nums">{{ $pendingCorrectionsCount }}</div>
                <p class="text-[11px] text-black/45 dark:text-white/45">Tiket butuh persetujuan</p>
            </div>
        </div>

        <!-- 3. TABEL LOG REKAP PRESENSI HARIAN -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-2xs space-y-3">
            <!-- Filter Bar -->
            <form method="GET" action="{{ route('hrm.index') }}" class="p-4 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-3">
                <input type="hidden" name="tab" value="attendance">

                <div class="flex flex-wrap items-center gap-2.5">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-black/40 dark:text-white/40 mb-1">Tanggal</label>
                        <input type="date" name="att_date" value="{{ $attDate }}"
                            class="h-8.5 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase font-bold text-black/40 dark:text-white/40 mb-1">Karyawan</label>
                        <select name="att_user_id" class="h-8.5 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                            <option value="">Semua Staf</option>
                            @foreach($memberships as $m)
                                @if($m->user)
                                    <option value="{{ $m->user->id }}" {{ $attUserId === $m->user->id ? 'selected' : '' }}>{{ $m->user->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase font-bold text-black/40 dark:text-white/40 mb-1">Status Kehadiran</label>
                        <select name="att_status" class="h-8.5 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                            <option value="">Semua Status</option>
                            <option value="present" {{ $attStatus === 'present' ? 'selected' : '' }}>Hadir Tepat Waktu</option>
                            <option value="late" {{ $attStatus === 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="half_day" {{ $attStatus === 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                            <option value="absent" {{ $attStatus === 'absent' ? 'selected' : '' }}>Alpa / Tidak Hadir</option>
                            <option value="leave" {{ $attStatus === 'leave' ? 'selected' : '' }}>Cuti Resmi</option>
                            <option value="sick" {{ $attStatus === 'sick' ? 'selected' : '' }}>Sakit</option>
                        </select>
                    </div>

                    <div class="flex items-end pt-4">
                        <button type="submit"
                            class="h-8.5 px-3.5 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black/80 dark:text-white/80 text-[12px] font-semibold transition cursor-pointer">
                            Filter
                        </button>
                    </div>
                </div>

                <div class="text-[12px] text-black/50 dark:text-white/50">
                    Total: <strong class="text-black dark:text-white">{{ $attendances->total() }}</strong> catatan
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.02] text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                            <th class="py-3 px-4 sm:px-5">Karyawan</th>
                            <th class="py-3 px-3">Tanggal</th>
                            <th class="py-3 px-3">Jam Masuk</th>
                            <th class="py-3 px-3">Jam Pulang</th>
                            <th class="py-3 px-3">Durasi</th>
                            <th class="py-3 px-3">Lokasi / Jarak</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-4 text-right">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($attendances as $att)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-4 sm:px-5 font-semibold text-black dark:text-white">
                                    {{ $att->user?->name ?? 'Staf' }}
                                    <div class="text-[11px] text-black/45 dark:text-white/45 font-normal">{{ $att->user?->email }}</div>
                                </td>
                                <td class="py-3 px-3 text-black/70 dark:text-white/70 tabular-nums">
                                    {{ $att->date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3 px-3 tabular-nums">
                                    @if($att->clock_in_at)
                                        <div class="font-semibold text-black dark:text-white">{{ $att->clock_in_at->format('H:i') }} WIB</div>
                                        @if($att->clock_in_status === 'late')
                                            <span class="text-[10px] text-[#FF9500] font-bold">Terlambat +{{ $att->late_minutes }}m</span>
                                        @elseif($att->clock_in_status === 'free_location')
                                            <span class="text-[10px] text-[#007AFF] font-medium">Bebas Lokasi</span>
                                        @else
                                            <span class="text-[10px] text-[#34C759] font-medium">Tepat Waktu</span>
                                        @endif
                                    @else
                                        <span class="text-black/35 dark:text-white/35">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 tabular-nums">
                                    @if($att->clock_out_at)
                                        <div class="font-semibold text-black dark:text-white">{{ $att->clock_out_at->format('H:i') }} WIB</div>
                                        @if($att->overtime_minutes > 0)
                                            <span class="text-[10px] text-[#007AFF] font-bold">+{{ $att->overtime_minutes }}m Lembur</span>
                                        @endif
                                    @else
                                        <span class="text-black/35 dark:text-white/35">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 tabular-nums font-medium text-black/80 dark:text-white/80">
                                    {{ $att->formatted_work_duration }}
                                </td>
                                <td class="py-3 px-3 text-[12px] text-black/60 dark:text-white/60">
                                    @if($att->clock_in_distance_meters !== null)
                                        <span class="tabular-nums font-semibold">{{ $att->clock_in_distance_meters }}m</span>
                                        <div class="text-[10px] text-black/40 dark:text-white/40">{{ $att->location?->name ?? 'Cabang' }}</div>
                                    @elseif($att->clock_in_status === 'free_location')
                                        <span class="text-[#007AFF] font-medium">Bebas Lokasi</span>
                                    @else
                                        <span class="text-black/35 dark:text-white/35">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    @if($att->status === 'present')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            Hadir
                                        </span>
                                    @elseif($att->status === 'late')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            Terlambat
                                        </span>
                                    @elseif($att->status === 'leave' || $att->status === 'sick')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#AF52DE]/15 text-[#AF52DE]">
                                            {{ ucfirst($att->status) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/15 text-[#FF3B30]">
                                            {{ ucfirst($att->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($att->is_corrected)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]" title="Data diperbarui via tiket koreksi">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Dikoreksi</span>
                                        </span>
                                    @else
                                        <span class="text-[11px] text-[#34C759] font-medium">Asli (Sensor)</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                                    Belum ada catatan presensi pada filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendances->hasPages())
                <div class="p-3 border-t border-black/5 dark:border-white/5">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 5: TIKET PERBAIKAN ABSENSI (PRD-06)                  -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'corrections'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-2xs">
            <div class="p-4 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Daftar Tiket Perbaikan Absensi</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Pengajuan perbaikan absensi resmi staf untuk transparansi data dan akurasi penggajian.</p>
                </div>
                <button type="button" @click="showAddCorrectionModal = true"
                    class="h-9 px-3.5 rounded-[9px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[12px] font-semibold flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(255,149,0,0.25)] transition active:scale-[0.97] cursor-pointer shrink-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Ajukan Tiket Koreksi</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.02] text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                            <th class="py-3.5 px-4 sm:px-5">No. Tiket</th>
                            <th class="py-3.5 px-3">Karyawan</th>
                            <th class="py-3.5 px-3">Tanggal Target</th>
                            <th class="py-3.5 px-3">Jenis Koreksi</th>
                            <th class="py-3.5 px-3">Usulan Jam</th>
                            <th class="py-3.5 px-3">Alasan Karyawan</th>
                            <th class="py-3.5 px-3">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi &amp; Tinjauan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($corrections as $cor)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white tabular-nums">
                                    {{ $cor->correction_number }}
                                </td>
                                <td class="py-3.5 px-3 font-medium text-black dark:text-white">
                                    {{ $cor->user?->name ?? 'Staf' }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black/70 dark:text-white/70">
                                    {{ $cor->target_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-3 text-black/80 dark:text-white/80">
                                    {{ $cor->type_label }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-[12px]">
                                    @if($cor->proposed_clock_in)
                                        <div>Masuk: <strong>{{ substr((string)$cor->proposed_clock_in, 0, 5) }}</strong></div>
                                    @endif
                                    @if($cor->proposed_clock_out)
                                        <div>Pulang: <strong>{{ substr((string)$cor->proposed_clock_out, 0, 5) }}</strong></div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 max-w-xs truncate text-[12px] text-black/60 dark:text-white/60" title="{{ $cor->reason }}">
                                    {{ $cor->reason }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($cor->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            Disetujui
                                        </span>
                                    @elseif($cor->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/15 text-[#FF3B30]">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            Menunggu Review
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <button type="button" @click="openReviewCorrection({{ json_encode($cor) }})"
                                        class="h-7.5 px-3 rounded-[8px] text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#0071E3] hover:text-white active:scale-[0.97] transition-all cursor-pointer">
                                        {{ $cor->status === 'pending' ? 'Tinjau Tiket' : 'Lihat Detail' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                                    Belum ada tiket pengajuan perbaikan absensi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($corrections->hasPages())
                <div class="p-3 border-t border-black/5 dark:border-white/5">
                    {{ $corrections->links() }}
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Mode Presensi Kehadiran <span class="text-[#FF3B30]">*</span></label>
                        <select name="attendance_mode" required class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="geofenced">Geofenced (Radius Kantor / Outlet)</option>
                            <option value="free">Bebas Lokasi (Sales Lapangan / Kurir / Remote)</option>
                        </select>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Staf sales & kurir dapat clock-in di mana saja tanpa terikat radius.</p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Penugasan Outlet / Kantor Utama</label>
                        <select name="primary_location_id" class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="">-- Lokasi Default Toko --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }} (Radius: {{ $loc->geofence_radius_meters ?? 50 }}m)</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Koordinat acuan untuk perhitungan radius geofencing.</p>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Mode Presensi Kehadiran <span class="text-[#FF3B30]">*</span></label>
                        <select name="attendance_mode" required class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="geofenced" :selected="selectedEmployee?.attendance_mode === 'geofenced'">Geofenced (Radius Kantor / Outlet)</option>
                            <option value="free" :selected="selectedEmployee?.attendance_mode === 'free'">Bebas Lokasi (Sales Lapangan / Kurir / Remote)</option>
                        </select>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Staf sales & kurir dapat clock-in di mana saja tanpa terikat radius.</p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Penugasan Outlet / Kantor Utama</label>
                        <select name="primary_location_id" class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="">-- Lokasi Default Toko --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" :selected="selectedEmployee?.primary_location_id === '{{ $loc->id }}'">{{ $loc->name }} (Radius: {{ $loc->geofence_radius_meters ?? 50 }}m)</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Koordinat acuan untuk perhitungan radius geofencing.</p>
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

    <!-- ======================================================== -->
    <!-- MODAL 4: PENGAJUAN TIKET PERBAIKAN ABSENSI (PRD-06)       -->
    <!-- ======================================================== -->
    <div x-show="showAddCorrectionModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;">
        <div @click.outside="showAddCorrectionModal = false" class="w-full max-w-xl bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.2)] overflow-hidden flex flex-col max-h-[92vh]">
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">Pengajuan Tiket Koreksi Absensi</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Ajukan revisi jam kerja apabila lupa clock-in/out atau ada kendala lapangan.</p>
                </div>
                <button type="button" @click="showAddCorrectionModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.attendance.corrections.store') }}" enctype="multipart/form-data" class="p-5 overflow-y-auto space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Absensi <span class="text-[#FF3B30]">*</span></label>
                        <input type="date" name="target_date" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Jenis Koreksi <span class="text-[#FF3B30]">*</span></label>
                        <select name="correction_type" required class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="clock_in_only">Lupa Clock-In (Hanya Jam Masuk)</option>
                            <option value="clock_out_only">Lupa Clock-Out (Hanya Jam Pulang)</option>
                            <option value="full_day">Perbaikan Seharian (Masuk & Pulang)</option>
                            <option value="status_only">Perbaikan Status (Izin / Sakit / Cuti)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Usulan Jam Masuk</label>
                        <input type="time" name="proposed_clock_in" value="08:30"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Usulan Jam Pulang</label>
                        <input type="time" name="proposed_clock_out" value="17:00"
                            class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Usulan Status</label>
                        <select name="proposed_status" class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="present">Hadir Tepat Waktu</option>
                            <option value="late">Hadir Terlambat</option>
                            <option value="half_day">Setengah Hari</option>
                            <option value="sick">Sakit (Lampirkan Surat)</option>
                            <option value="leave">Izin / Cuti</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Alasan Pengajuan Revisi <span class="text-[#FF3B30]">*</span></label>
                    <textarea name="reason" required minlength="5" maxlength="1000" rows="3"
                        placeholder="Jelaskan kendala secara jujur (contoh: HP lowbat, GPS lambat mengunci saat hujan, tugas dinas luar outlet...)"
                        class="w-full p-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Lampiran Bukti (Opsional)</label>
                    <input type="file" name="attachment" accept="image/png,image/jpeg,image/webp,application/pdf"
                        class="w-full text-[12px] text-black/70 dark:text-white/70 file:mr-3 file:py-2 file:px-3.5 file:rounded-[8px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 cursor-pointer">
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Format: JPG, PNG, PDF. Maksimal 5MB (surat dokter, foto presensi manual, dsb).</p>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddCorrectionModal = false"
                        class="h-9 px-4 rounded-[9px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-9 px-4 rounded-[9px] text-[12px] font-semibold text-white bg-[#FF9500] hover:bg-[#E08500] transition active:scale-[0.97] cursor-pointer">
                        Kirim Tiket Koreksi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 5: VISUAL DIFF MODAL SHEET REVIEW TIKET (PRD-06)   -->
    <!-- ======================================================== -->
    <div x-show="showReviewCorrectionModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;">
        <div @click.outside="showReviewCorrectionModal = false" class="w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_28px_56px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[92vh]">
            <!-- Header -->
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-[16px] font-bold text-black dark:text-white">Tiket Koreksi:</span>
                        <span class="font-mono text-[14px] font-bold text-[#007AFF] px-2 py-0.5 rounded-[6px] bg-[#007AFF]/10" x-text="selectedCorrection?.correction_number"></span>
                        <template x-if="selectedCorrection?.status === 'pending'">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">Menunggu Review</span>
                        </template>
                        <template x-if="selectedCorrection?.status === 'approved'">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">Disetujui</span>
                        </template>
                        <template x-if="selectedCorrection?.status === 'rejected'">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">Ditolak</span>
                        </template>
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50">
                        Pengaju: <strong class="text-black dark:text-white" x-text="selectedCorrection?.user?.name || 'Karyawan'"></strong>
                        &bull; Target: <span class="tabular-nums" x-text="selectedCorrection?.target_date ? selectedCorrection.target_date.substring(0, 10) : '-'"></span>
                    </p>
                </div>
                <button type="button" @click="showReviewCorrectionModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-5 overflow-y-auto space-y-5 text-[13px]">
                <!-- Bento Visual Diff Comparison -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Visual Diff: Rekaman Sensor vs Usulan Revisi</span>
                        <span class="text-[11px] text-[#007AFF] font-medium" x-text="selectedCorrection?.type_label || 'Koreksi'"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Left: Data Sistem Asli -->
                        <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3">
                            <div class="flex items-center gap-1.5 text-black/60 dark:text-white/60 font-semibold text-[12px]">
                                <i data-lucide="database" class="w-3.5 h-3.5"></i>
                                <span>Rekaman Sensor Sistem Asli</span>
                            </div>

                            <div class="space-y-2 text-[12px]">
                                <div class="flex justify-between">
                                    <span class="text-black/50 dark:text-white/50">Clock-In:</span>
                                    <span class="font-semibold tabular-nums text-black dark:text-white"
                                        x-text="selectedCorrection?.attendance?.clock_in_at ? selectedCorrection.attendance.clock_in_at.substring(11, 16) + ' WIB' : 'Belum Ada / Tidak Tercatat'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-black/50 dark:text-white/50">Clock-Out:</span>
                                    <span class="font-semibold tabular-nums text-black dark:text-white"
                                        x-text="selectedCorrection?.attendance?.clock_out_at ? selectedCorrection.attendance.clock_out_at.substring(11, 16) + ' WIB' : 'Belum Ada / Tidak Tercatat'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-black/50 dark:text-white/50">Status:</span>
                                    <span class="font-semibold text-black dark:text-white uppercase"
                                        x-text="selectedCorrection?.attendance?.status || 'TIDAK HADIR / ALPHA'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-black/50 dark:text-white/50">Durasi Kerja:</span>
                                    <span class="font-semibold tabular-nums text-black dark:text-white"
                                        x-text="selectedCorrection?.attendance?.work_duration_minutes ? Math.floor(selectedCorrection.attendance.work_duration_minutes / 60) + 'j ' + (selectedCorrection.attendance.work_duration_minutes % 60) + 'm' : '-'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Usulan Staf (Diff Highlighted) -->
                        <div class="p-4 rounded-[14px] bg-[#007AFF]/[0.06] border border-[#007AFF]/20 space-y-3">
                            <div class="flex items-center gap-1.5 text-[#007AFF] font-semibold text-[12px]">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Usulan Perbaikan Staf</span>
                            </div>

                            <div class="space-y-2 text-[12px]">
                                <div class="flex justify-between">
                                    <span class="text-black/50 dark:text-white/50">Usulan Masuk:</span>
                                    <span class="font-bold tabular-nums text-[#007AFF]"
                                        x-text="selectedCorrection?.proposed_clock_in ? selectedCorrection.proposed_clock_in.substring(0, 5) + ' WIB' : '-'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-black/50 dark:text-white/50">Usulan Pulang:</span>
                                    <span class="font-bold tabular-nums text-[#007AFF]"
                                        x-text="selectedCorrection?.proposed_clock_out ? selectedCorrection.proposed_clock_out.substring(0, 5) + ' WIB' : '-'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-black/50 dark:text-white/50">Usulan Status:</span>
                                    <span class="font-bold uppercase text-[#007AFF]"
                                        x-text="selectedCorrection?.proposed_status || '-'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-black/50 dark:text-white/50">Status Tiket:</span>
                                    <span class="font-bold uppercase"
                                        :class="selectedCorrection?.status === 'approved' ? 'text-[#34C759]' : (selectedCorrection?.status === 'rejected' ? 'text-[#FF3B30]' : 'text-[#FF9500]')"
                                        x-text="selectedCorrection?.status"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alasan Pengajuan -->
                <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 block mb-1">Alasan Staf:</span>
                    <p class="text-[13px] text-black/80 dark:text-white/80 leading-relaxed italic" x-text="selectedCorrection?.reason"></p>
                </div>

                <!-- Lampiran Bukti File -->
                <template x-if="selectedCorrection?.attachment_path">
                    <div class="flex items-center justify-between p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2 min-w-0">
                            <i data-lucide="paperclip" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span class="text-[12px] text-black/70 dark:text-white/70 truncate">Dokumen Bukti Lampiran Terlampir</span>
                        </div>
                        <a :href="'/storage/' + selectedCorrection.attachment_path" target="_blank"
                            class="h-7.5 px-3 rounded-[7px] text-[11px] font-semibold bg-[#007AFF] text-white hover:bg-[#0071E3] transition flex items-center gap-1 shrink-0">
                            <i data-lucide="external-link" class="w-3 h-3"></i>
                            <span>Buka Berkas</span>
                        </a>
                    </div>
                </template>

                <!-- Riwayat Keputusan Jika Sudah Di-review -->
                <template x-if="selectedCorrection?.status !== 'pending'">
                    <div class="p-3.5 rounded-[12px] border"
                        :class="selectedCorrection?.status === 'approved' ? 'bg-[#34C759]/10 border-[#34C759]/20' : 'bg-[#FF3B30]/10 border-[#FF3B30]/20'">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[12px] font-bold"
                                :class="selectedCorrection?.status === 'approved' ? 'text-[#34C759]' : 'text-[#FF3B30]'"
                                x-text="selectedCorrection?.status === 'approved' ? 'Tiket Telah Disetujui' : 'Tiket Telah Ditolak'"></span>
                            <span class="text-[11px] text-black/45 dark:text-white/45 tabular-nums"
                                x-text="selectedCorrection?.reviewed_at ? selectedCorrection.reviewed_at.substring(0, 16) : ''"></span>
                        </div>
                        <p class="text-[12px] text-black/75 dark:text-white/75"
                            x-text="selectedCorrection?.review_notes || selectedCorrection?.reason || 'Tidak ada catatan tambahan.'"></p>
                    </div>
                </template>

                <!-- Action Verification Buttons (Hanya Tampil Jika Status Pending) -->
                <template x-if="selectedCorrection?.status === 'pending'">
                    <div class="pt-3 border-t border-black/5 dark:border-white/10 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Approve Form -->
                            <form method="POST" :action="'{{ url('hrm/attendance/corrections') }}/' + selectedCorrection?.id + '/approve'" class="space-y-2">
                                @csrf
                                <input type="text" name="review_notes" placeholder="Catatan persetujuan (opsional)"
                                    class="w-full h-8 px-2.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[11px] text-black dark:text-white focus:ring-1 focus:ring-[#34C759]">
                                <button type="submit"
                                    class="w-full h-9 rounded-[9px] bg-[#34C759] hover:bg-[#28A745] text-white text-[12px] font-semibold flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)] transition active:scale-[0.97] cursor-pointer">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>Setujui Tiket &amp; Sinkronkan</span>
                                </button>
                            </form>

                            <!-- Reject Form -->
                            <form method="POST" :action="'{{ url('hrm/attendance/corrections') }}/' + selectedCorrection?.id + '/reject'" class="space-y-2">
                                @csrf
                                <input type="text" name="reason" required minlength="3" placeholder="Alasan penolakan (wajib)"
                                    class="w-full h-8 px-2.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[11px] text-black dark:text-white focus:ring-1 focus:ring-[#FF3B30]">
                                <button type="submit"
                                    class="w-full h-9 rounded-[9px] bg-[#FF3B30] hover:bg-[#D70015] text-white text-[12px] font-semibold flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(255,59,48,0.25)] transition active:scale-[0.97] cursor-pointer">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    <span>Tolak Pengajuan</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
