@extends('layouts.app')

@section('title', 'Portal Karyawan & Presensi - ' . $business->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6"
    x-data="portalAttendance({
        todayAttendance: {{ Js::from($todayAttendance) }},
        defaultLat: {{ $defaultLat }},
        defaultLng: {{ $defaultLng }},
        cityName: '{{ addslashes($cityName) }}',
        clockInRoute: '{{ route('hrm.attendance.clock-in') }}',
        clockOutRoute: '{{ route('hrm.attendance.clock-out') }}',
        csrfToken: '{{ csrf_token() }}'
    })">

    {{-- Alert Notification Banner (Flash Messages or Permission Warning) --}}
    @if (session('error'))
        <div class="flex items-center gap-3 p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-sm font-medium animate-fadeIn">
            <i data-lucide="shield-alert" class="w-5 h-5 shrink-0"></i>
            <div class="flex-1">
                <span class="font-semibold">Akses Terbatas:</span> {{ session('error') }}
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="p-1 hover:bg-[#FF3B30]/10 rounded-[8px] transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    @if (session('success'))
        <div class="flex items-center gap-3 p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#34C759] text-sm font-medium animate-fadeIn">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
            <div class="flex-1">
                {{ session('success') }}
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="p-1 hover:bg-[#34C759]/10 rounded-[8px] transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    {{-- Interactive Dynamic Toast Banner --}}
    <div x-show="toast.show" x-transition.opacity.duration.250ms
        :class="toast.type === 'error' ? 'bg-[#FF3B30]/10 border-[#FF3B30]/30 text-[#FF3B30]' : 'bg-[#34C759]/10 border-[#34C759]/30 text-[#34C759]'"
        class="flex items-center gap-3 p-4 rounded-[16px] border text-sm font-medium shadow-sm"
        style="display: none;">
        <i :data-lucide="toast.type === 'error' ? 'alert-circle' : 'check-circle-2'" class="w-5 h-5 shrink-0"></i>
        <div class="flex-1" x-text="toast.message"></div>
        <button type="button" @click="toast.show = false" class="p-1 hover:bg-black/5 dark:hover:bg-white/10 rounded-[8px] transition">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    {{-- ======================================================== --}}
    {{-- CENTERPIECE: BENTO HERO (JAM WIB, CUACA, USER & ABSENSI)  --}}
    {{-- ======================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- 1. Main Digital Clock & Live Weather Card (7 Cols) --}}
        <div class="lg:col-span-7 rounded-[24px] bg-gradient-to-br from-white/90 via-white/80 to-slate-50/70 dark:from-[#1C1C1E]/95 dark:via-[#1C1C1E]/80 dark:to-[#141416]/70 backdrop-blur-2xl border border-black/5 dark:border-white/10 p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] flex flex-col justify-between relative overflow-hidden group">
            {{-- Ambient Glow Decorative Circles --}}
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-[#007AFF]/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-[#34C759]/10 rounded-full blur-3xl pointer-events-none"></div>

            {{-- Top Row: Location Tag & WIB Timezone Badge --}}
            <div class="flex items-center justify-between gap-3 relative z-10">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 text-[12px] font-semibold text-black/75 dark:text-white/75">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span x-text="cityName || '{{ $cityName }}'">{{ $cityName }}</span>
                    <span class="text-black/30 dark:text-white/30">•</span>
                    <span class="text-black/50 dark:text-white/50 text-[11px]">{{ $location?->name ?? 'Kantor Utama' }}</span>
                </div>

                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] text-[11px] font-bold tracking-wide">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                    <span>WIB (UTC+7)</span>
                </div>
            </div>

            {{-- Center: Live Real-Time WIB Digital Clock --}}
            <div class="my-6 sm:my-8 text-center sm:text-left relative z-10">
                <div class="flex items-baseline justify-center sm:justify-start gap-2.5">
                    <div class="text-5xl sm:text-6xl md:text-7xl font-bold tracking-tight text-slate-900 dark:text-white font-mono tabular-nums select-none"
                        x-text="timeString">
                        {{ now('Asia/Jakarta')->format('H:i:s') }}
                    </div>
                    <span class="text-lg sm:text-xl font-bold tracking-wider text-[#007AFF] uppercase font-mono">
                        WIB
                    </span>
                </div>

                {{-- Full Indonesian Date --}}
                <div class="mt-2 text-sm sm:text-base font-medium text-slate-600 dark:text-slate-300 flex items-center justify-center sm:justify-start gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                    <span x-text="dateString">{{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>

            {{-- Bottom: Live Weather Widget --}}
            <div class="relative z-10 pt-4 border-t border-black/5 dark:border-white/10 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 dark:bg-[#0A84FF]/20 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                        <i :data-lucide="weather.icon" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums font-mono" x-text="weather.temp !== null ? weather.temp + '°C' : '--°C'">
                                --°C
                            </span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="weather.condition">
                                Memuat cuaca...
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-2 mt-0.5">
                            <span x-show="weather.humidity !== null">Kelembapan: <strong class="font-semibold text-slate-600 dark:text-slate-300" x-text="weather.humidity + '%'"></strong></span>
                            <span x-show="weather.wind !== null">• Angin: <strong class="font-semibold text-slate-600 dark:text-slate-300" x-text="weather.wind + ' km/h'"></strong></span>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-[10.5px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500 block">Status Shift</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold mt-1"
                        :class="shiftStatusClass">
                        <span class="w-1.5 h-1.5 rounded-full" :class="shiftDotClass"></span>
                        <span x-text="shiftStatusText">Memeriksa...</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- 2. User Identity & Live Attendance Action Bento Card (5 Cols) --}}
        <div class="lg:col-span-5 rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-2xl border border-black/5 dark:border-white/10 p-6 sm:p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] flex flex-col justify-between">
            {{-- User Header Tile --}}
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-[18px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center font-bold text-xl shadow-md shadow-[#007AFF]/25 shrink-0 select-none">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white truncate tracking-tight">
                            {{ $user->name }}
                        </h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                            {{ $membership?->roleModel?->name ?? ucfirst($membership?->role ?? 'Staf') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ $user->email }}</p>
                    <div class="flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                        <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                        <span class="truncate">{{ $business->name }}</span>
                    </div>
                </div>
            </div>

            {{-- Live Working Timer / Today Status Pill --}}
            <div class="my-5 p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5">
                <div class="flex items-center justify-between text-xs mb-2">
                    <span class="font-medium text-slate-500 dark:text-slate-400">Presensi Hari Ini:</span>
                    <span class="font-bold text-slate-900 dark:text-white tabular-nums" x-text="todayStatusTitle"></span>
                </div>

                {{-- Status Bar with Clock-in & Clock-out Times --}}
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Jam Masuk</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100 font-mono tabular-nums mt-0.5 block"
                            x-text="attendance ? (attendance.clock_in_at ? formatTime(attendance.clock_in_at) : '--:--') : '--:--'">
                            {{ $todayAttendance?->clock_in_at ? $todayAttendance->clock_in_at->format('H:i') . ' WIB' : '--:--' }}
                        </span>
                    </div>
                    <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Jam Pulang</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100 font-mono tabular-nums mt-0.5 block"
                            x-text="attendance ? (attendance.clock_out_at ? formatTime(attendance.clock_out_at) : '--:--') : '--:--'">
                            {{ $todayAttendance?->clock_out_at ? $todayAttendance->clock_out_at->format('H:i') . ' WIB' : '--:--' }}
                        </span>
                    </div>
                </div>

                {{-- Live Duration Counter (When clocked in but not clocked out) --}}
                <template x-if="attendance && attendance.clock_in_at && !attendance.clock_out_at">
                    <div class="mt-3 flex items-center justify-center gap-2 py-1 px-3 rounded-full bg-[#34C759]/10 text-[#34C759] text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                        <span>Durasi Bertugas: <strong class="font-mono tabular-nums" x-text="liveDurationText">00:00:00</strong></span>
                    </div>
                </template>
            </div>

            {{-- Action Buttons: Clock In & Clock Out --}}
            <div class="space-y-2.5">
                {{-- Button 1: Absen Masuk --}}
                <button type="button"
                    @click="performClockIn()"
                    :disabled="isSubmitting || (attendance && attendance.clock_in_at)"
                    :class="(attendance && attendance.clock_in_at) ? 'opacity-40 cursor-not-allowed bg-slate-200 dark:bg-slate-800 text-slate-500' : 'bg-[#34C759] hover:bg-[#2FB34F] active:scale-[0.98] text-white shadow-md shadow-[#34C759]/25 font-semibold'"
                    class="w-full h-12 rounded-[14px] flex items-center justify-center gap-2 text-sm font-semibold transition-all">
                    <template x-if="isSubmitting && activeAction === 'clock-in'">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                    </template>
                    <template x-if="!(isSubmitting && activeAction === 'clock-in')">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                    </template>
                    <span x-text="(attendance && attendance.clock_in_at) ? 'Sudah Absen Masuk' : 'Absen Masuk (Clock-In)'"></span>
                </button>

                {{-- Button 2: Absen Pulang --}}
                <button type="button"
                    @click="performClockOut()"
                    :disabled="isSubmitting || !attendance || !attendance.clock_in_at || (attendance && attendance.clock_out_at)"
                    :class="(!attendance || !attendance.clock_in_at || (attendance && attendance.clock_out_at)) ? 'opacity-40 cursor-not-allowed bg-slate-200 dark:bg-slate-800 text-slate-500' : 'bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.98] text-white shadow-md shadow-[#FF9500]/25 font-semibold'"
                    class="w-full h-12 rounded-[14px] flex items-center justify-center gap-2 text-sm font-semibold transition-all">
                    <template x-if="isSubmitting && activeAction === 'clock-out'">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                    </template>
                    <template x-if="!(isSubmitting && activeAction === 'clock-out')">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </template>
                    <span x-text="(attendance && attendance.clock_out_at) ? 'Sudah Absen Pulang' : 'Absen Pulang (Clock-Out)'"></span>
                </button>
            </div>
        </div>

    </div>

    {{-- ======================================================== --}}
    {{-- SECTION: QUICK WORKSTATIONS (MODUL YANG BERHAK DIAKSES)   --}}
    {{-- ======================================================== --}}
    @if (count($quickModules) > 0)
        <div class="rounded-[20px] bg-white/60 dark:bg-[#1C1C1E]/60 backdrop-blur-xl border border-black/5 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-[#007AFF]"></i>
                        Akses Cepat Modul Kerja
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Modul operasional yang terhubung dengan akun Anda</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-slate-600 dark:text-slate-300">
                    {{ count($quickModules) }} Modul Tersedia
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach ($quickModules as $mod)
                    <a href="{{ $mod['route'] }}"
                        class="p-3.5 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 hover:border-black/15 dark:hover:border-white/20 hover:shadow-md transition-all active:scale-[0.98] group flex flex-col justify-between">
                        <div class="w-10 h-10 rounded-[12px] flex items-center justify-center text-white mb-2 transition-transform group-hover:scale-110"
                            style="background-color: {{ $mod['color'] }}; box-shadow: 0 4px 12px {{ $mod['color'] }}33;">
                            <i data-lucide="{{ $mod['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-[#007AFF] transition-colors block truncate">
                                {{ $mod['name'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 line-clamp-1 mt-0.5">
                                {{ $mod['desc'] }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- SECTION: HISTORI PRESENSI 7 HARI TERAKHIR               --}}
    {{-- ======================================================== --}}
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-2xl border border-black/5 dark:border-white/10 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)]">
        {{-- Section Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <i data-lucide="history" class="w-4.5 h-4.5 text-[#34C759]"></i>
                    Histori Presensi 7 Hari Terakhir
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Catatan kehadiran, jam masuk, jam keluar, dan durasi kerja Anda
                </p>
            </div>

            {{-- 7-Day Performance Stat Badges --}}
            <div class="flex items-center gap-2 flex-wrap">
                <div class="px-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Hadir</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 tabular-nums font-mono">{{ $presentDaysCount }} Hari</span>
                </div>
                <div class="px-3 py-1.5 rounded-[10px] bg-[#34C759]/10 border border-[#34C759]/20 text-center">
                    <span class="text-[10px] uppercase font-bold text-[#34C759] block">Tepat Waktu</span>
                    <span class="text-xs font-bold text-[#34C759] tabular-nums font-mono">{{ $onTimeDaysCount }} Hari</span>
                </div>
                <div class="px-3 py-1.5 rounded-[10px] bg-[#FF9500]/10 border border-[#FF9500]/20 text-center">
                    <span class="text-[10px] uppercase font-bold text-[#FF9500] block">Terlambat</span>
                    <span class="text-xs font-bold text-[#FF9500] tabular-nums font-mono">{{ $lateDaysCount }} Hari</span>
                </div>
                <div class="px-3 py-1.5 rounded-[10px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-center">
                    <span class="text-[10px] uppercase font-bold text-[#007AFF] block">Total Jam Kerja</span>
                    <span class="text-xs font-bold text-[#007AFF] tabular-nums font-mono">{{ $totalWorkHours }} Jam</span>
                </div>
            </div>
        </div>

        {{-- Table for Desktop --}}
        <div class="hidden sm:block overflow-x-auto mt-4">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-black/5 dark:border-white/5">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Jam Masuk</th>
                        <th class="py-3 px-4">Jam Keluar</th>
                        <th class="py-3 px-4">Durasi Kerja</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Lokasi / Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse ($recentAttendances as $item)
                        @php
                            $isToday = $item->date?->toDateString() === now('Asia/Jakarta')->toDateString();
                        @endphp
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition {{ $isToday ? 'bg-[#007AFF]/[0.03]' : '' }}">
                            <td class="py-3.5 px-4 font-medium text-slate-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <span>{{ $item->date ? $item->date->translatedFormat('l, d M Y') : '-' }}</span>
                                    @if ($isToday)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#007AFF]/10 text-[#007AFF]">Hari Ini</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono tabular-nums text-slate-700 dark:text-slate-300">
                                @if ($item->clock_in_at)
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="log-in" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                        <span>{{ $item->clock_in_at->format('H:i') }} WIB</span>
                                        @if ($item->clock_in_status === \App\Models\Attendance::CLOCK_IN_LATE)
                                            <span class="text-[10px] text-[#FF9500] font-sans font-bold">({{ $item->late_minutes }}m telat)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono tabular-nums text-slate-700 dark:text-slate-300">
                                @if ($item->clock_out_at)
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="log-out" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                        <span>{{ $item->clock_out_at->format('H:i') }} WIB</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Belum Pulang</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono tabular-nums text-slate-700 dark:text-slate-300">
                                @if ($item->work_duration_minutes)
                                    @php
                                        $h = intdiv($item->work_duration_minutes, 60);
                                        $m = $item->work_duration_minutes % 60;
                                    @endphp
                                    <span>{{ $h }}j {{ $m }}m</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($item->status === \App\Models\Attendance::STATUS_PRESENT)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30">
                                        Hadir Tepat
                                    </span>
                                @elseif ($item->status === \App\Models\Attendance::STATUS_LATE)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30">
                                        Terlambat
                                    </span>
                                @elseif ($item->status === \App\Models\Attendance::STATUS_HALF_DAY)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#5856D6]/15 text-[#5856D6] border border-[#5856D6]/30">
                                        Setengah Hari
                                    </span>
                                @elseif ($item->status === \App\Models\Attendance::STATUS_LEAVE)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30">
                                        Cuti / Izin
                                    </span>
                                @elseif ($item->status === \App\Models\Attendance::STATUS_SICK)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#AF52DE]/15 text-[#AF52DE] border border-[#AF52DE]/30">
                                        Sakit
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        {{ ucfirst($item->status ?? 'Tercatat') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right text-slate-500 dark:text-slate-400">
                                <span class="truncate block max-w-[200px] ml-auto">
                                    {{ $item->location?->name ?? $item->clock_in_address ?? 'Presensi Kantor' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-medium">Belum ada riwayat absensi dalam 7 hari terakhir.</p>
                                <p class="text-xs mt-1">Lakukan Absen Masuk di atas saat memulai jam kerja Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards for Small Screens --}}
        <div class="block sm:hidden divide-y divide-black/5 dark:divide-white/5 mt-4">
            @forelse ($recentAttendances as $item)
                @php
                    $isToday = $item->date?->toDateString() === now('Asia/Jakarta')->toDateString();
                @endphp
                <div class="py-4 space-y-2 {{ $isToday ? 'bg-[#007AFF]/[0.03] p-3 rounded-[14px]' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white text-xs">
                            {{ $item->date ? $item->date->translatedFormat('D, d M Y') : '-' }}
                        </span>
                        @if ($item->status === \App\Models\Attendance::STATUS_PRESENT)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759]">Hadir</span>
                        @elseif ($item->status === \App\Models\Attendance::STATUS_LATE)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500]">Terlambat</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600">
                                {{ ucfirst($item->status ?? 'Tercatat') }}
                            </span>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-[11px] font-mono tabular-nums text-slate-600 dark:text-slate-300">
                        <div>Masuk: <strong>{{ $item->clock_in_at ? $item->clock_in_at->format('H:i') . ' WIB' : '-' }}</strong></div>
                        <div>Pulang: <strong>{{ $item->clock_out_at ? $item->clock_out_at->format('H:i') . ' WIB' : '-' }}</strong></div>
                    </div>
                    @if ($item->work_duration_minutes)
                        <div class="text-[11px] text-slate-500">
                            Durasi: <strong class="text-slate-700 dark:text-slate-200">{{ intdiv($item->work_duration_minutes, 60) }}j {{ $item->work_duration_minutes % 60 }}m</strong>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-8 text-center text-slate-400">
                    <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                    <p class="text-xs">Belum ada riwayat absensi 7 hari terakhir.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Alpine.js Portal & Attendance Center Reactive Script --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('portalAttendance', (config) => ({
        attendance: config.todayAttendance,
        cityName: config.cityName || 'Jakarta',
        defaultLat: config.defaultLat || -6.2088,
        defaultLng: config.defaultLng || 106.8456,
        clockInRoute: config.clockInRoute,
        clockOutRoute: config.clockOutRoute,
        csrfToken: config.csrfToken,

        timeString: '',
        dateString: '',
        liveDurationText: '00:00:00',

        weather: {
            temp: null,
            condition: 'Memuat data cuaca...',
            humidity: null,
            wind: null,
            icon: 'cloud-sun'
        },

        toast: {
            show: false,
            type: 'success',
            message: ''
        },

        isSubmitting: false,
        activeAction: null,

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.fetchWeather();
            if (window.lucide) {
                this.$nextTick(() => window.lucide.createIcons());
            }
        },

        updateClock() {
            const now = new Date();
            // WIB Time formatting
            const optionsTime = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const timeFormatter = new Intl.DateTimeFormat('id-ID', optionsTime);
            this.timeString = timeFormatter.format(now).replace(/\./g, ':');

            const optionsDate = { timeZone: 'Asia/Jakarta', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateFormatter = new Intl.DateTimeFormat('id-ID', optionsDate);
            this.dateString = dateFormatter.format(now);

            // Calculate live work duration if clocked in and not clocked out
            if (this.attendance && this.attendance.clock_in_at && !this.attendance.clock_out_at) {
                const clockInTime = new Date(this.attendance.clock_in_at);
                const diffMs = Math.max(0, now - clockInTime);
                const totalSeconds = Math.floor(diffMs / 1000);
                const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
                const seconds = String(totalSeconds % 60).padStart(2, '0');
                this.liveDurationText = `${hours}:${minutes}:${seconds}`;
            }
        },

        async fetchWeather() {
            try {
                const lat = this.defaultLat;
                const lng = this.defaultLng;
                const res = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&timezone=Asia%2FJakarta`);
                if (!res.ok) throw new Error('Weather API error');
                const data = await res.json();
                if (data.current) {
                    this.weather.temp = Math.round(data.current.temperature_2m);
                    this.weather.humidity = Math.round(data.current.relative_humidity_2m);
                    this.weather.wind = Math.round(data.current.wind_speed_10m);
                    const code = data.current.weather_code;
                    this.weather.condition = this.mapWeatherCode(code);
                    this.weather.icon = this.mapWeatherIcon(code);
                }
            } catch (e) {
                this.weather.temp = 29;
                this.weather.condition = 'Cerah Berawan';
                this.weather.humidity = 75;
                this.weather.wind = 8;
                this.weather.icon = 'cloud-sun';
            } finally {
                if (window.lucide) {
                    this.$nextTick(() => window.lucide.createIcons());
                }
            }
        },

        mapWeatherCode(code) {
            if (code === 0) return 'Cerah Terik';
            if (code <= 3) return 'Cerah Berawan';
            if (code <= 48) return 'Berkabut';
            if (code <= 67) return 'Hujan Ringan';
            if (code <= 82) return 'Hujan Lebat';
            return 'Badai Petir';
        },

        mapWeatherIcon(code) {
            if (code === 0) return 'sun';
            if (code <= 3) return 'cloud-sun';
            if (code <= 48) return 'cloud-fog';
            if (code <= 67) return 'cloud-rain';
            if (code <= 82) return 'cloud-rain-wind';
            return 'cloud-lightning';
        },

        formatTime(dateStr) {
            if (!dateStr) return '--:--';
            const d = new Date(dateStr);
            const hours = String(d.getHours()).padStart(2, '0');
            const mins = String(d.getMinutes()).padStart(2, '0');
            return `${hours}:${mins} WIB`;
        },

        get todayStatusTitle() {
            if (!this.attendance || !this.attendance.clock_in_at) {
                return 'Belum Absen Masuk';
            }
            if (this.attendance.clock_out_at) {
                return 'Selesai Bertugas';
            }
            return 'Sedang Bertugas';
        },

        get shiftStatusClass() {
            if (!this.attendance || !this.attendance.clock_in_at) {
                return 'bg-[#FF9500]/10 text-[#FF9500] border border-[#FF9500]/20';
            }
            if (this.attendance.clock_out_at) {
                return 'bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20';
            }
            return 'bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20';
        },

        get shiftDotClass() {
            if (!this.attendance || !this.attendance.clock_in_at) return 'bg-[#FF9500]';
            if (this.attendance.clock_out_at) return 'bg-[#007AFF]';
            return 'bg-[#34C759] animate-ping';
        },

        get shiftStatusText() {
            if (!this.attendance || !this.attendance.clock_in_at) return 'Belum Masuk';
            if (this.attendance.clock_out_at) return 'Shift Selesai';
            return 'Aktif Bekerja';
        },

        async getCoordinates() {
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    return resolve({});
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => resolve({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy: pos.coords.accuracy
                    }),
                    () => resolve({}),
                    { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                );
            });
        },

        async performClockIn() {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            this.activeAction = 'clock-in';

            try {
                const coords = await this.getCoordinates();
                const payload = {
                    ...coords,
                    notes: 'Presensi via Portal Karyawan'
                };

                const res = await fetch(this.clockInRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal mencatat presensi masuk.');
                }

                this.attendance = data.attendance;
                this.showToast('success', data.message || 'Presensi masuk berhasil dicatat!');
                setTimeout(() => window.location.reload(), 1500);
            } catch (err) {
                this.showToast('error', err.message);
            } finally {
                this.isSubmitting = false;
                this.activeAction = null;
                if (window.lucide) this.$nextTick(() => window.lucide.createIcons());
            }
        },

        async performClockOut() {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            this.activeAction = 'clock-out';

            try {
                const coords = await this.getCoordinates();
                const payload = {
                    ...coords,
                    notes: 'Presensi pulang via Portal Karyawan'
                };

                const res = await fetch(this.clockOutRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal mencatat presensi pulang.');
                }

                this.attendance = data.attendance;
                this.showToast('success', data.message || 'Presensi pulang berhasil dicatat!');
                setTimeout(() => window.location.reload(), 1500);
            } catch (err) {
                this.showToast('error', err.message);
            } finally {
                this.isSubmitting = false;
                this.activeAction = null;
                if (window.lucide) this.$nextTick(() => window.lucide.createIcons());
            }
        },

        showToast(type, message) {
            this.toast.type = type;
            this.toast.message = message;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 6000);
        }
    }));
});
</script>
@endsection
