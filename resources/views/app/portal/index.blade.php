@extends('layouts.app')

@section('title', 'Portal Kepegawaian & Presensi - ' . $business->name)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-container {
            font-family: inherit;
            z-index: 10;
            border-radius: 18px;
        }
        .custom-map-marker {
            background: transparent;
            border: none;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 py-6 sm:py-8 pb-28 sm:pb-32 space-y-6"
    x-data="portalAttendance({
        activeTab: '{{ $activeTab }}',
        todayAttendance: {{ Js::from($todayAttendance) }},
        activeException: {{ Js::from($activeException) }},
        defaultLat: {{ $defaultLat }},
        defaultLng: {{ $defaultLng }},
        cityName: '{{ addslashes($cityName) }}',
        officeName: '{{ addslashes($location?->name ?? __('portal.office_location_label')) }}',
        allowedRadius: {{ (int) ($location?->geofence_radius_meters ?: 50) }},
        clockInRoute: '{{ route('hrm.attendance.clock-in') }}',
        clockOutRoute: '{{ route('hrm.attendance.clock-out') }}',
        faceRegisterRoute: '{{ route('portal.face-register') }}',
        faceVerifyRoute: '{{ route('portal.face-verify') }}',
        hasFaceRegistered: {{ $membership?->hasFaceRegistered() ? 'true' : 'false' }},
        timezone: '{{ $timezone ?? 'Asia/Jakarta' }}',
        tzAbbr: '{{ $tzAbbr ?? 'WIB' }}',
        csrfToken: '{{ csrf_token() }}',
        i18n: {
            faceVerifying: '{{ __('portal.face_verifying') }}',
            faceVerifiedSuccess: '{{ __('portal.face_verified_success', ['score' => '85']) }}',
            faceFrontalGuide: '{{ __('portal.face_frontal_guide') }}',
            mapTitle: '{{ __('portal.map_title') }}',
            mapUserLocation: '{{ __('portal.map_user_location') }}',
            mapOfficeLocation: '{{ __('portal.map_office_location') }}',
            mapStatusInside: '{{ __('portal.map_status_inside') }}',
            mapStatusOutside: '{{ __('portal.map_status_outside') }}',
            faceRegisteredSuccess: '{{ __('portal.msg_face_registered') }}',
            weatherLoading: '{{ __('portal.loading_weather') }}'
        }
    })">

    {{-- 1. ALERT NOTIFICATIONS --}}
    @if (session('error'))
        <div class="flex items-center gap-3 p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-sm font-medium animate-fadeIn">
            <i data-lucide="shield-alert" class="w-5 h-5 shrink-0"></i>
            <div class="flex-1">
                <span class="font-semibold">{{ __('portal.access_validation_failed') }}</span> {{ session('error') }}
            </div>
            <button type="button" @click="$el.parentElement.remove()"
                aria-label="{{ __('common.close') }}"
                class="p-1 hover:bg-[#FF3B30]/10 rounded-[8px] transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    @if (session('success'))
        <div class="flex items-center gap-3 p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#34C759] text-sm font-medium animate-fadeIn">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
            <div class="flex-1 font-medium">
                {{ session('success') }}
            </div>
            <button type="button" @click="$el.parentElement.remove()"
                aria-label="{{ __('common.close') }}"
                class="p-1 hover:bg-[#34C759]/10 rounded-[8px] transition cursor-pointer">
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
        <div class="flex-1 font-medium" x-text="toast.message"></div>
        <button type="button" @click="toast.show = false"
            aria-label="{{ __('common.close') }}"
            class="p-1 hover:bg-black/5 dark:hover:bg-white/10 rounded-[8px] transition cursor-pointer">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    {{-- 2. PORTAL 3-ROW PAGE HEADER (APPLE HIG STANDARDIZED) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#007AFF] dark:text-[#0A84FF]">
                {{ __('portal.title') }}
            </p>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-black dark:text-white">
                    {{ __('portal.greeting', ['name' => $user->name]) }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] border border-[#007AFF]/20">
                    {{ $membership?->roleModel?->name ?? ($membership?->job_title ?? __('hrm.staff_label')) }}
                </span>
            </div>
            <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 font-medium">
                {{ $business->name }} &bull; {{ __('portal.profile_tenure') }}: {{ $tenureText }} &bull; {{ $cityName }}
            </p>
        </div>

        {{-- Active Workplace Badge & Biometric Status Pill --}}
        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            @if($membership?->hasFaceRegistered())
                <div class="px-3.5 py-2 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-[#34C759] block">{{ __('portal.modal_face_reg_title') }}</span>
                        <span class="text-xs font-bold text-[#34C759]">{{ __('portal.biometric_status_registered') }}</span>
                    </div>
                </div>
            @else
                <button type="button" @click="openFaceRegisterModal()"
                    class="px-3.5 py-2 min-h-[44px] rounded-[14px] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 border border-[#FF9500]/30 flex items-center gap-2 transition active:scale-[0.98] cursor-pointer">
                    <i data-lucide="scan-face" class="w-4 h-4 text-[#FF9500] animate-pulse"></i>
                    <div class="text-left">
                        <span class="text-[10px] uppercase font-bold text-[#FF9500] block">{{ __('portal.modal_face_reg_title') }}</span>
                        <span class="text-xs font-bold text-[#FF9500]">{{ __('portal.biometric_status_action') }}</span>
                    </div>
                </button>
            @endif

            <div class="px-3.5 py-2 min-h-[44px] rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 flex flex-col justify-center text-right">
                <span class="text-[10px] uppercase font-bold text-black/40 dark:text-white/40 block">{{ __('portal.workspace_label') }}</span>
                <span class="text-xs font-bold text-black dark:text-white truncate block max-w-[180px]">{{ $business->name }}</span>
            </div>
        </div>
    </div>

    {{-- 3. APPLE HIG SEGMENTED CONTROLS BAR --}}
    <div class="p-1.5 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 grid grid-cols-2 md:grid-cols-4 gap-1.5">
        <button type="button" @click="setTab('attendance')"
            :class="activeTab === 'attendance' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
            class="py-2.5 px-3 min-h-[44px] rounded-[12px] text-xs sm:text-[13px] flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer">
            <i data-lucide="scan-face" class="w-4 h-4 text-[#007AFF]"></i>
            <span>{{ __('portal.tab_attendance') }}</span>
        </button>

        <button type="button" @click="setTab('profile')"
            :class="activeTab === 'profile' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
            class="py-2.5 px-3 min-h-[44px] rounded-[12px] text-xs sm:text-[13px] flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer">
            <i data-lucide="user-check" class="w-4 h-4 text-[#34C759]"></i>
            <span>{{ __('portal.tab_profile') }}</span>
        </button>

        <button type="button" @click="setTab('payslips')"
            :class="activeTab === 'payslips' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
            class="py-2.5 px-3 min-h-[44px] rounded-[12px] text-xs sm:text-[13px] flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer">
            <i data-lucide="badge-cent" class="w-4 h-4 text-[#FF9500]"></i>
            <span>{{ __('portal.tab_payslips', ['count' => count($payslips)]) }}</span>
        </button>

        <button type="button" @click="setTab('history')"
            :class="activeTab === 'history' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
            class="py-2.5 px-3 min-h-[44px] rounded-[12px] text-xs sm:text-[13px] flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer">
            <i data-lucide="file-clock" class="w-4 h-4 text-[#AF52DE]"></i>
            <span>{{ __('portal.tab_history') }}</span>
        </button>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 1: PRESENSI & ABSENSI MULTI-POSE LIVENESS            --}}
    {{-- ======================================================== --}}
    <div x-show="activeTab === 'attendance'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

            {{-- 1. Main Digital Clock & Live Weather Card (7 Cols) --}}
            <div class="lg:col-span-7 rounded-[24px] bg-gradient-to-br from-white/90 via-white/80 to-slate-50/70 dark:from-[#1C1C1E]/95 dark:via-[#1C1C1E]/80 dark:to-[#141416]/70 backdrop-blur-2xl border border-black/5 dark:border-white/10 p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-[#007AFF]/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-[#34C759]/10 rounded-full blur-3xl pointer-events-none"></div>

                {{-- Top Row: Location Tag & WIB Timezone Badge --}}
                <div class="flex items-center justify-between gap-3 relative z-10">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 text-[12px] font-semibold text-black/75 dark:text-white/75">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span x-text="cityName || '{{ $cityName }}'">{{ $cityName }}</span>
                        <span class="text-black/30 dark:text-white/30">•</span>
                        <span class="text-black/50 dark:text-white/50 text-[11px]">{{ $location?->name ?? __('portal.office_location_label') }} (Radius {{ (int) ($location?->geofence_radius_meters ?: 50) }}m)</span>
                    </div>

                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] text-[11px] font-bold tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                        <span>{{ $tzAbbr ?? 'WIB' }} ({{ $timezone ?? 'Asia/Jakarta' }})</span>
                    </div>
                </div>

                {{-- Center: Live Real-Time Digital Clock & Active Work Shift --}}
                <div class="my-6 sm:my-8 text-center sm:text-left relative z-10">
                    <div class="flex items-baseline justify-center sm:justify-start gap-2.5">
                        <div class="text-5xl sm:text-6xl md:text-7xl font-bold tracking-tight text-slate-900 dark:text-white font-mono tabular-nums select-none"
                            x-text="timeString">
                            {{ ($localNow ?? now())->format('H:i:s') }}
                        </div>
                        <span class="text-lg sm:text-xl font-bold tracking-wider text-[#007AFF] uppercase font-mono">
                            {{ $tzAbbr ?? 'WIB' }}
                        </span>
                    </div>

                    <div class="mt-2 text-sm sm:text-base font-medium text-slate-600 dark:text-slate-300 flex items-center justify-center sm:justify-start gap-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                        <span x-text="dateString">{{ ($localNow ?? now())->translatedFormat('l, d F Y') }}</span>
                    </div>

                    {{-- Active Work Shift Card --}}
                    <div class="mt-4 p-3 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 inline-flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div class="text-left">
                            <div class="text-[10px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                Jadwal Shift Hari Ini
                            </div>
                            <div class="text-xs font-bold text-black dark:text-white flex items-center gap-2 flex-wrap">
                                <span>{{ $activeShift['shift_name'] ?? 'Bebas Jadwal' }}</span>
                                @if(!empty($activeShift['is_off_day']))
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#FF3B30]/10 text-[#FF3B30]">Libur / OFF</span>
                                @elseif($activeShift['has_schedule'] ?? false)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] tabular-nums">
                                        {{ $activeShift['scheduled_start'] }} - {{ $activeShift['scheduled_end'] }}
                                        @if($activeShift['is_overnight'] ?? false) (+1) @endif
                                    </span>
                                    @if(($activeShift['grace_period_minutes'] ?? 0) > 0)
                                        <span class="text-[10px] font-medium text-black/50 dark:text-white/50">Toleransi: {{ $activeShift['grace_period_minutes'] }}m</span>
                                    @endif
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-500/10 text-slate-500">Tanpa Shift Terikat</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom: Live Weather & Biometric Security Notice --}}
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
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="weather.condition || '{{ __('portal.loading_weather') }}'">
                                    {{ __('portal.loading_weather') }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-2 mt-0.5">
                                <span x-show="weather.humidity !== null">{{ __('portal.humidity_label') }}: <strong class="font-semibold text-slate-600 dark:text-slate-300" x-text="weather.humidity + '%'"></strong></span>
                                <span x-show="weather.wind !== null">• {{ __('portal.wind_label') }}: <strong class="font-semibold text-slate-600 dark:text-slate-300" x-text="weather.wind + ' km/h'"></strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="text-[10.5px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500 block">{{ __('portal.today_status_label') }}</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold mt-1"
                            :class="shiftStatusClass">
                            <span class="w-1.5 h-1.5 rounded-full" :class="shiftDotClass"></span>
                            <span x-text="shiftStatusText">...</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2. User Identity & Live Face Attendance Action Card (5 Cols) --}}
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
                                {{ $membership?->roleModel?->name ?? ucfirst($membership?->role ?? __('hrm.staff_label')) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ $user->email }}</p>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                            <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                            <span class="truncate">{{ $business->name }}</span>
                        </div>
                    </div>
                </div>

                {{-- Status Bar with Clock-in & Clock-out Times --}}
                <div class="my-5 p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-slate-500 dark:text-slate-400">{{ __('portal.today_status_label') }}:</span>
                        <span class="font-bold text-slate-900 dark:text-white tabular-nums" x-text="todayStatusTitle"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">{{ __('portal.clock_in_title') }}</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100 font-mono tabular-nums mt-0.5 block"
                                x-text="attendance ? (attendance.clock_in_at ? formatTime(attendance.clock_in_at) : '--:--') : '--:--'">
                                {{ $todayAttendance?->clock_in_at ? $todayAttendance->clock_in_at->format('H:i') . ' WIB' : '--:--' }}
                            </span>
                        </div>
                        <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">{{ __('portal.clock_out_title') }}</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100 font-mono tabular-nums mt-0.5 block"
                                x-text="attendance ? (attendance.clock_out_at ? formatTime(attendance.clock_out_at) : '--:--') : '--:--'">
                                {{ $todayAttendance?->clock_out_at ? $todayAttendance->clock_out_at->format('H:i') . ' WIB' : '--:--' }}
                            </span>
                        </div>
                    </div>

                    {{-- Biometric Match Score Badge if Present --}}
                    <template x-if="attendance && attendance.face_verified">
                        <div class="flex items-center justify-between py-1.5 px-3 rounded-[10px] bg-[#34C759]/10 text-[#34C759] text-[11px] font-bold">
                            <span class="flex items-center gap-1.5"><i data-lucide="shield-check" class="w-3.5 h-3.5"></i> {{ __('portal.biometric_verified_badge') }}</span>
                            <span class="font-mono tabular-nums" x-text="attendance.face_similarity_score ? (Math.round(attendance.face_similarity_score * 100) + '% Cocok') : '{{ __('portal.biometric_liveness_pass') }}'"></span>
                        </div>
                    </template>

                    {{-- Live Working Timer --}}
                    <template x-if="attendance && attendance.clock_in_at && !attendance.clock_out_at">
                        <div class="flex items-center justify-center gap-2 py-1.5 px-3 rounded-full bg-[#34C759]/10 text-[#34C759] text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span>{{ __('portal.duty_duration') }}: <strong class="font-mono tabular-nums" x-text="liveDurationText">00:00:00</strong></span>
                        </div>
                    </template>

                    {{-- Warning Banner if Face Not Registered Yet --}}
                    <template x-if="!hasFaceRegistered">
                        <div class="p-3.5 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 flex items-start gap-2.5 text-xs text-[#FF9500]">
                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <div class="flex-1 space-y-1">
                                <span class="font-bold block text-black dark:text-white">Biometrik Wajah Belum Terdaftar</span>
                                <p class="text-[11.5px] text-black/70 dark:text-white/70">Presensi wajib dicocokkan dengan biometrik wajah. Harap daftarkan wajah Anda terlebih dahulu.</p>
                                <button type="button" @click="openFaceRegisterModal()" class="mt-1 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-[#FF9500] text-white font-bold text-xs shadow-xs hover:bg-[#E08500] transition active:scale-95 cursor-pointer">
                                    <i data-lucide="scan-face" class="w-3.5 h-3.5"></i> Daftarkan Wajah Sekarang
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Action Buttons: 1-Direction Frontal Face Biometric + Geofence Live Map --}}
                <div class="space-y-2.5">
                    <button type="button"
                        @click="startFaceAttendance('in')"
                        :disabled="isSubmitting || (attendance && attendance.clock_in_at)"
                        :class="(attendance && attendance.clock_in_at) ? 'opacity-40 cursor-not-allowed bg-slate-200 dark:bg-slate-800 text-slate-500' : 'bg-[#34C759] hover:bg-[#2FB34F] active:scale-[0.98] text-white shadow-md shadow-[#34C759]/25 font-bold'"
                        class="w-full h-14 sm:h-13 min-h-[48px] rounded-[16px] flex items-center justify-center gap-2.5 text-[15px] font-bold transition-all cursor-pointer">
                        <i data-lucide="scan-face" class="w-5 h-5"></i>
                        <span x-text="(attendance && attendance.clock_in_at) ? (window.__('portal.action_clocked_in_already') || '{{ __('portal.action_clocked_in_already') }}') : (window.__('portal.action_clock_in') || '{{ __('portal.action_clock_in') }}')">{{ __('portal.action_clock_in') }}</span>
                    </button>

                    <button type="button"
                        @click="startFaceAttendance('out')"
                        :disabled="isSubmitting || !attendance || !attendance.clock_in_at || (attendance && attendance.clock_out_at)"
                        :class="(!attendance || !attendance.clock_in_at || (attendance && attendance.clock_out_at)) ? 'opacity-40 cursor-not-allowed bg-slate-200 dark:bg-slate-800 text-slate-500' : 'bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.98] text-white shadow-md shadow-[#FF9500]/25 font-bold'"
                        class="w-full h-14 sm:h-13 min-h-[48px] rounded-[16px] flex items-center justify-center gap-2.5 text-[15px] font-bold transition-all cursor-pointer">
                        <i data-lucide="scan-face" class="w-5 h-5"></i>
                        <span x-text="(attendance && attendance.clock_out_at) ? (window.__('portal.action_clocked_out_already') || '{{ __('portal.action_clocked_out_already') }}') : (window.__('portal.action_clock_out') || '{{ __('portal.action_clock_out') }}')">{{ __('portal.action_clock_out') }}</span>
                    </button>
                </div>
            </div>

        </div>

        {{-- Quick Workstations Section --}}
        @if (count($quickModules) > 0)
            <div class="rounded-[20px] bg-white/60 dark:bg-[#1C1C1E]/60 backdrop-blur-xl border border-black/5 dark:border-white/10 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i data-lucide="layout-grid" class="w-4 h-4 text-[#007AFF]"></i>
                            {{ __('portal.workstations_title') }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('portal.workstations_subtitle') }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-slate-600 dark:text-slate-300">
                        {{ __('portal.workstations_available', ['count' => count($quickModules)]) }}
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
        @else
            <div class="rounded-[20px] bg-white/40 dark:bg-[#1C1C1E]/40 border border-black/5 dark:border-white/10 p-5 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-slate-400 shrink-0">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ __('portal.workstations_empty_title') }}</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('portal.workstations_empty_desc') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- 7-Day Performance Snapshot --}}
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-2xl border border-black/5 dark:border-white/10 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)]">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/5 dark:border-white/10">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="history" class="w-4.5 h-4.5 text-[#34C759]"></i>
                        {{ __('portal.history_7days_title') }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ __('portal.history_7days_subtitle') }}
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <div class="px-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-center">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">{{ __('portal.metric_present_month') }}</span>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 tabular-nums font-mono">{{ __('portal.days_worked_count', ['count' => $presentDaysCount]) }}</span>
                    </div>
                    <div class="px-3 py-1.5 rounded-[10px] bg-[#34C759]/10 border border-[#34C759]/20 text-center">
                        <span class="text-[10px] uppercase font-bold text-[#34C759] block">{{ __('portal.metric_on_time') }}</span>
                        <span class="text-xs font-bold text-[#34C759] tabular-nums font-mono">{{ __('portal.days_worked_count', ['count' => $onTimeDaysCount]) }}</span>
                    </div>
                    <div class="px-3 py-1.5 rounded-[10px] bg-[#FF9500]/10 border border-[#FF9500]/20 text-center">
                        <span class="text-[10px] uppercase font-bold text-[#FF9500] block">{{ __('portal.metric_late') }}</span>
                        <span class="text-xs font-bold text-[#FF9500] tabular-nums font-mono">{{ __('portal.days_worked_count', ['count' => $lateDaysCount]) }}</span>
                    </div>
                    <div class="px-3 py-1.5 rounded-[10px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-center">
                        <span class="text-[10px] uppercase font-bold text-[#007AFF] block">{{ __('portal.total_work_hours') }}</span>
                        <span class="text-xs font-bold text-[#007AFF] tabular-nums font-mono">{{ __('portal.metric_work_hours', ['count' => $totalWorkHours]) }}</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-black/5 dark:border-white/5">
                            <th class="py-3 px-4">{{ __('portal.col_date') }}</th>
                            <th class="py-3 px-4">{{ __('portal.col_clock_in') }}</th>
                            <th class="py-3 px-4">{{ __('portal.col_clock_out') }}</th>
                            <th class="py-3 px-4">{{ __('portal.col_work_duration') }}</th>
                            <th class="py-3 px-4">{{ __('portal.col_biometric') }}</th>
                            <th class="py-3 px-4">{{ __('portal.col_status') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('portal.col_location') }}</th>
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
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#007AFF]/10 text-[#007AFF]">{{ __('portal.today') }}</span>
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
                                        <span class="text-slate-400 italic">{{ __('portal.not_checked_out') }}</span>
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
                                    @if ($item->face_verified)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759]">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>{{ __('portal.biometric_liveness_pass') }}</span>
                                        </span>
                                    @else
                                        <span class="text-black/40 dark:text-white/40 text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($item->status === \App\Models\Attendance::STATUS_PRESENT)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30">
                                            {{ __('hrm.attendance_status_present') }}
                                        </span>
                                    @elseif ($item->status === \App\Models\Attendance::STATUS_LATE)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30">
                                            {{ __('hrm.attendance_status_late') }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            {{ ucfirst($item->status ?? 'Tercatat') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right text-slate-500 dark:text-slate-400">
                                    <span class="truncate block max-w-[180px] ml-auto">
                                        {{ $item->location?->name ?? $item->clock_in_address ?? __('portal.office_location_label') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600"></i>
                                    <p class="text-sm font-medium">{{ __('portal.empty_attendance_logs') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 2: INFORMASI KEPEGAWAIAN, BPJS & BIOMETRIK           --}}
    {{-- ======================================================== --}}
    <div x-show="activeTab === 'profile'" class="space-y-6" style="display: none;">
        
        {{-- Row 1: Apple Wallet ID Card + Biometrik & Cuti --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            
            {{-- Digital Employee Pass (5 Cols) --}}
            <div class="lg:col-span-5 rounded-[24px] bg-gradient-to-br from-[#1C1C1E] via-[#2C2C2E] to-[#0A84FF]/20 text-white p-6 sm:p-7 shadow-xl border border-white/10 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-[#007AFF]/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-white/10 flex items-center justify-center font-bold text-sm">
                            <i data-lucide="badge-check" class="w-5 h-5 text-[#30D158]"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-white/50 block">{{ __('portal.official_id_card') }}</span>
                            <span class="text-xs font-bold text-white">{{ $business->name }}</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-white/10 text-white border border-white/15">
                        {{ $membership?->employment_type === 'contract' ? __('portal.employment_type_contract') : ($membership?->employment_type === 'daily_worker' ? __('portal.employment_type_daily') : __('portal.employment_type_permanent')) }}
                    </span>
                </div>

                <div class="my-6 space-y-1">
                    <div class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                        {{ $user->name }}
                    </div>
                    <div class="text-sm font-medium text-[#0A84FF]">
                        {{ $membership?->job_title ?? __('portal.default_staff_title') }}
                    </div>
                    <div class="text-xs text-white/50 pt-2 flex items-center gap-2">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        <span>{{ $user->email }}</span>
                    </div>
                    @if ($membership?->whatsapp_number)
                        <div class="text-xs text-white/50 flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                            <span>{{ $membership->whatsapp_number }}</span>
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-white/10 flex items-center justify-between text-xs text-white/70">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-white/40 block">{{ __('portal.profile_tenure') }}</span>
                        <span class="font-bold text-white tabular-nums">{{ $tenureText }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-white/40 block">{{ __('portal.joined_since') }}</span>
                        <span class="font-bold text-white">{{ $membership?->join_date ? $membership->join_date->translatedFormat('d M Y') : $user->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- 4 Bento Tiles: Biometrik Wajah, Cuti, BPJS & Payroll --}}
            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                {{-- Tile 1: Status Biometrik Wajah & Enkripsi --}}
                <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="scan-face" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-black dark:text-white uppercase tracking-wider">{{ __('portal.col_biometric') }}</h4>
                                <span class="text-[11px] text-black/50 dark:text-white/50">{{ __('portal.aes_encryption') }}</span>
                            </div>
                        </div>
                        @if($membership?->hasFaceRegistered())
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759]">
                                {{ __('portal.registered') }}
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#FF9500]/10 text-[#FF9500]">
                                {{ __('portal.not_registered') }}
                            </span>
                        @endif
                    </div>

                    <div class="my-3 space-y-1.5 text-xs">
                        <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                            @if($membership?->hasFaceRegistered())
                                {{ __('portal.biometric_enrolled_date', ['date' => $membership->face_registered_at ? $membership->face_registered_at->translatedFormat('d M Y H:i') : 'System']) }}
                            @else
                                {{ __('portal.biometric_not_enrolled_desc') }}
                            @endif
                        </p>
                    </div>

                    <button type="button" @click="openFaceRegisterModal()"
                        class="w-full h-11 sm:h-10 min-h-[44px] sm:min-h-0 rounded-[12px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] text-[13px] sm:text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <span>{{ $membership?->hasFaceRegistered() ? __('portal.btn_update_face') : __('portal.btn_register_face_now') }}</span>
                    </button>
                </div>

                {{-- Tile 2: Kuota & Sisa Cuti Tahunan --}}
                <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="calendar-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-black dark:text-white uppercase tracking-wider">{{ __('portal.leaves_annual_title') }}</h4>
                                <span class="text-[11px] text-black/50 dark:text-white/50">{{ __('portal.leaves_year_label', ['year' => now()->year]) }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#34C759]/10 text-[#34C759]">
                            {{ __('portal.days_remaining', ['count' => $leaveRemaining]) }}
                        </span>
                    </div>

                    <div class="my-3 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-black/60 dark:text-white/60">{{ __('portal.leave_used_label') }}</span>
                            <span class="font-bold text-black dark:text-white">{{ __('portal.leave_days_used_of', ['used' => $leaveUsed, 'total' => $leaveAllowance]) }}</span>
                        </div>
                        @php
                            $usedPct = min(100, round(($leaveUsed / max(1, $leaveAllowance)) * 100));
                        @endphp
                        <div class="w-full h-2 rounded-full bg-black/[0.06] dark:bg-white/[0.08] overflow-hidden">
                            <div class="h-full bg-[#007AFF] rounded-full" style="width: {{ $usedPct }}%"></div>
                        </div>
                    </div>

                    <p class="text-[11px] text-black/45 dark:text-white/45">
                        {{ __('portal.leaves_reset_notice') }}
                    </p>
                </div>

                {{-- Tile 3: BPJS Ketenagakerjaan & BPJS Kesehatan --}}
                <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-black dark:text-white uppercase tracking-wider">{{ __('portal.bpjs_social_security') }}</h4>
                            <span class="text-[11px] text-black/50 dark:text-white/50">{{ __('portal.bpjs_status_participant') }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-black/60 dark:text-white/60">BPJS TK:</span>
                            @if ($membership?->bpjs_tk_enabled)
                                <div class="text-right">
                                    <span class="font-bold text-[#34C759] flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> {{ __('portal.active') }}</span>
                                    <span class="font-mono text-[11px] text-black/70 dark:text-white/70 block">{{ $membership->bpjs_tk_number ?: '-' }}</span>
                                </div>
                            @else
                                <span class="text-black/40 dark:text-white/40">{{ __('portal.inactive') }}</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-black/60 dark:text-white/60">BPJS Kesehatan:</span>
                            @if ($membership?->bpjs_kes_enabled)
                                <div class="text-right">
                                    <span class="font-bold text-[#34C759] flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> {{ __('portal.active') }}</span>
                                    <span class="font-mono text-[11px] text-black/70 dark:text-white/70 block">{{ $membership->bpjs_kes_number ?: '-' }}</span>
                                </div>
                            @else
                                <span class="text-black/40 dark:text-white/40">{{ __('portal.inactive') }}</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-black/5 dark:border-white/5">
                            <span class="text-black/60 dark:text-white/60">{{ __('portal.dependents_label') }}</span>
                            <span class="font-bold text-black dark:text-white">{{ (int) $membership?->bpjs_dependents_count }}</span>
                        </div>
                    </div>
                </div>

                {{-- Tile 4: Rekening Pembayaran Gaji --}}
                <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-9 h-9 rounded-[12px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-black dark:text-white uppercase tracking-wider">{{ __('portal.payroll_bank_account') }}</h4>
                            <span class="text-[11px] text-black/50 dark:text-white/50">{{ __('portal.salary_transfer_note') }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-black/50 dark:text-white/50">{{ __('portal.bank_label') }}</span>
                            <span class="font-bold text-black dark:text-white">{{ $membership?->bank_name ?? __('portal.bank_not_set') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-black/50 dark:text-white/50">{{ __('portal.account_number_label') }}</span>
                            <span class="font-mono font-bold text-black dark:text-white tracking-wider">{{ $membership?->bank_account_number ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-black/50 dark:text-white/50">{{ __('portal.account_holder_label') }}</span>
                            <span class="font-medium text-black/75 dark:text-white/75 truncate max-w-[140px]">{{ $membership?->bank_account_holder ?? $user->name }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Row 2: Informasi Detail Kompensasi, NIK, NPWP & Penugasan --}}
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-2xl border border-black/5 dark:border-white/10 p-6 sm:p-7 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <i data-lucide="coins" class="w-5 h-5 text-[#34C759]"></i>
                {{ __('portal.compensation_package_title') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.salary_base_label') }}</span>
                    <span class="text-base font-bold font-mono text-black dark:text-white tabular-nums mt-1 block">
                        Rp {{ number_format((float) ($membership?->base_salary ?? 0), 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-black/40 dark:text-white/40">{{ __('portal.per_month_label') }}</span>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.fixed_allowances_label') }}</span>
                    <span class="text-base font-bold font-mono text-[#34C759] tabular-nums mt-1 block">
                        Rp {{ number_format((float) ($membership?->fixed_allowances ?? 0), 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-black/40 dark:text-white/40">{{ __('portal.allowances_desc') }}</span>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.nik_npwp_label') }}</span>
                    <div class="font-mono text-xs font-bold text-black dark:text-white mt-1">
                        <div>NIK: {{ $membership?->nik_ktp ?: '-' }}</div>
                        <div>NPWP: {{ $membership?->npwp ?: '-' }}</div>
                    </div>
                    <span class="text-[10px] text-black/40 dark:text-white/40">{{ __('portal.ptkp_status_label', ['status' => $membership?->tax_ptkp_status ?? 'TK/0']) }}</span>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.primary_assigned_branch') }}</span>
                    <span class="text-sm font-bold text-black dark:text-white mt-1 block truncate">
                        {{ $location?->name ?? __('portal.default_main_office') }}
                    </span>
                    <span class="text-[10px] text-black/40 dark:text-white/40">{{ $cityName }} ({{ __('portal.radius_meters', ['radius' => (int) ($location?->geofence_radius_meters ?: 50)]) }})</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ======================================================== --}}
    {{-- TAB 3: SLIP GAJI DIGITAL LENGKAP                         --}}
    {{-- ======================================================== --}}
    <div x-show="activeTab === 'payslips'" class="space-y-6" style="display: none;">
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-2xl border border-black/5 dark:border-white/10 p-6 sm:p-7 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/5 dark:border-white/10">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="receipt" class="w-5 h-5 text-[#FF9500]"></i>
                        {{ __('portal.payslips_archive_title') }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ __('portal.payslips_archive_subtitle') }}
                    </p>
                </div>

                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-[#FF9500]/10 text-[#FF9500]">
                    {{ __('portal.payslips_issued_count', ['count' => count($payslips)]) }}
                </span>
            </div>

            <div class="divide-y divide-black/5 dark:divide-white/5 mt-4">
                @forelse ($payslips as $slip)
                    @php
                        $periodName = $slip->payroll?->formatted_period ?? $slip->created_at->translatedFormat('F Y');
                        $isPaid = $slip->status === \App\Models\PayrollItem::STATUS_PAID;
                    @endphp
                    <div class="py-4 sm:py-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-black/[0.01] dark:hover:bg-white/[0.01] rounded-[16px] px-3 transition">
                        <div class="flex items-start gap-3.5">
                            <div class="w-12 h-12 rounded-[14px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="file-text" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-bold text-black dark:text-white">
                                        {{ __('portal.slip_period_label', ['period' => $periodName]) }}
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isPaid ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF9500]/10 text-[#FF9500]' }}">
                                        {{ $isPaid ? __('portal.paid_off') : __('portal.approved') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-black/50 dark:text-white/50 mt-1">
                                    <span>{{ __('portal.gross_pay_label') }} <strong class="text-black dark:text-white font-mono">Rp {{ number_format((float) $slip->gross_pay, 0, ',', '.') }}</strong></span>
                                    <span>•</span>
                                    <span>{{ __('portal.deductions_label') }} <strong class="text-[#FF3B30] font-mono">Rp {{ number_format((float) $slip->total_deductions, 0, ',', '.') }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-4 border-t md:border-t-0 pt-3 md:pt-0 border-black/5 dark:border-white/5">
                            <div class="text-left md:text-right">
                                <span class="text-[10px] uppercase font-bold text-black/40 dark:text-white/40 block">{{ __('portal.take_home_pay_label') }}</span>
                                <span class="text-base sm:text-lg font-bold font-mono text-[#34C759] tabular-nums">
                                    Rp {{ number_format((float) $slip->take_home_pay, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('portal.payslips.show', $slip->id) }}"
                                    class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold flex items-center gap-1.5 shadow-xs transition active:scale-[0.98]">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('portal.view_slip') }}</span>
                                </a>

                                <a href="{{ route('public.payslip', $slip->payslip_token) }}" target="_blank"
                                    title="{{ __('portal.tooltip_open_pdf') }}"
                                    class="h-9 w-9 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black dark:text-white flex items-center justify-center transition active:scale-[0.98]">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 dark:text-slate-500">
                        <i data-lucide="badge-cent" class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-slate-600"></i>
                        <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('portal.payslips_empty_title') }}</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                            {{ __('portal.payslips_empty_desc') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 4: RIWAYAT BULANAN & TIKET KOREKSI ABSENSI           --}}
    {{-- ======================================================== --}}
    <div x-show="activeTab === 'history'" class="space-y-6" style="display: none;">
        
        {{-- Monthly Stats Bento Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.metric_present_month') }}</span>
                <span class="text-lg font-bold text-[#34C759] tabular-nums font-mono mt-1 block">{{ __('portal.days_suffix', ['count' => $monthlyStats['present']]) }}</span>
            </div>
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.metric_late') }}</span>
                <span class="text-lg font-bold text-[#FF9500] tabular-nums font-mono mt-1 block">{{ __('portal.days_suffix', ['count' => $monthlyStats['late']]) }}</span>
            </div>
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.leave_permission') }}</span>
                <span class="text-lg font-bold text-[#007AFF] tabular-nums font-mono mt-1 block">{{ __('portal.days_suffix', ['count' => $monthlyStats['leave']]) }}</span>
            </div>
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.sick') }}</span>
                <span class="text-lg font-bold text-[#AF52DE] tabular-nums font-mono mt-1 block">{{ __('portal.days_suffix', ['count' => $monthlyStats['sick']]) }}</span>
            </div>
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.total_overtime') }}</span>
                <span class="text-lg font-bold text-[#5856D6] tabular-nums font-mono mt-1 block">{{ __('portal.hours_suffix', ['count' => round($monthlyStats['overtime_minutes'] / 60, 1)]) }}</span>
            </div>
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.metric_work_hours_label') }}</span>
                <span class="text-lg font-bold text-black dark:text-white tabular-nums font-mono mt-1 block">{{ __('portal.hours_suffix', ['count' => $monthlyStats['total_hours']]) }}</span>
            </div>
        </div>

        {{-- Section 1: Pengajuan Tiket Koreksi & Tiket Aktif --}}
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-2xl border border-black/5 dark:border-white/10 p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="file-check" class="w-5 h-5 text-[#007AFF]"></i>
                        {{ __('portal.tickets_corrections_title') }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ __('portal.correction_reason_desc') }}
                    </p>
                </div>

                <button type="button" @click="showCorrectionModal = true"
                    class="h-11 sm:h-9 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] sm:rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] sm:text-xs font-semibold flex items-center justify-center gap-1.5 shadow-xs transition active:scale-[0.98] cursor-pointer w-full sm:w-auto">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('portal.apply_correction') }}</span>
                </button>
            </div>

            <div class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($attendanceCorrections as $ticket)
                    <div class="py-3.5 flex items-center justify-between gap-3 text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-black dark:text-white">{{ $ticket->correction_number }}</span>
                                <span class="text-black/40">•</span>
                                <span class="font-medium text-black/75 dark:text-white/75">{{ $ticket->target_date ? $ticket->target_date->translatedFormat('d M Y') : '-' }}</span>
                                @if ($ticket->status === \App\Models\AttendanceCorrection::STATUS_APPROVED)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759]">{{ __('portal.approved') }}</span>
                                @elseif ($ticket->status === \App\Models\AttendanceCorrection::STATUS_REJECTED)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/10 text-[#FF3B30]">{{ __('portal.rejected') }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/10 text-[#FF9500]">{{ __('portal.waiting_hr_review') }}</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">{{ __('portal.correction_reason_label', ['reason' => $ticket->reason]) }}</p>
                        </div>

                        <div class="text-right text-[11px] font-mono tabular-nums text-black/60 dark:text-white/60">
                            <span>{{ __('portal.requested_at', ['time' => $ticket->created_at->translatedFormat('d/m/Y H:i')]) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400">
                        <p class="text-xs">{{ __('portal.no_tickets_history') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Section 2: Tabel Presensi Bulan Berjalan --}}
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-2xl border border-black/5 dark:border-white/10 p-6 shadow-xs">
            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2 mb-4">
                <i data-lucide="calendar" class="w-5 h-5 text-[#34C759]"></i>
                {{ __('portal.monthly_attendance_summary', ['month' => now('Asia/Jakarta')->translatedFormat('F Y')]) }}
            </h3>

            {{-- Desktop Table View (>= 768px) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-black/5 dark:border-white/5">
                            <th class="py-3 px-3">{{ __('portal.col_date') }}</th>
                            <th class="py-3 px-3">{{ __('portal.col_clock_in') }}</th>
                            <th class="py-3 px-3">{{ __('portal.col_clock_out') }}</th>
                            <th class="py-3 px-3">{{ __('portal.col_total_hours') }}</th>
                            <th class="py-3 px-3">{{ __('portal.col_overtime') }}</th>
                            <th class="py-3 px-3">{{ __('portal.col_status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse ($monthlyAttendances as $row)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                <td class="py-3 px-3 font-medium text-slate-900 dark:text-white">
                                    {{ $row->date ? $row->date->translatedFormat('l, d M Y') : '-' }}
                                </td>
                                <td class="py-3 px-3 font-mono tabular-nums">
                                    {{ $row->clock_in_at ? $row->clock_in_at->format('H:i') . ' WIB' : '-' }}
                                </td>
                                <td class="py-3 px-3 font-mono tabular-nums">
                                    {{ $row->clock_out_at ? $row->clock_out_at->format('H:i') . ' WIB' : '-' }}
                                </td>
                                <td class="py-3 px-3 font-mono tabular-nums">
                                    {{ $row->work_duration_minutes ? __('portal.hours_suffix', ['count' => round($row->work_duration_minutes / 60, 1)]) : '-' }}
                                </td>
                                <td class="py-3 px-3 font-mono tabular-nums">
                                    {{ $row->overtime_minutes > 0 ? __('portal.hours_suffix', ['count' => round($row->overtime_minutes / 60, 1)]) : '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $row->status === \App\Models\Attendance::STATUS_PRESENT ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF9500]/10 text-[#FF9500]' }}">
                                        {{ ucfirst($row->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    {{ __('portal.empty_attendance_logs') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Bento Card View (< 768px) --}}
            <div class="md:hidden space-y-3">
                @forelse ($monthlyAttendances as $row)
                    @php
                        $isPresent = ($row->status === \App\Models\Attendance::STATUS_PRESENT);
                        $isLate = ($row->status === \App\Models\Attendance::STATUS_LATE || ($row->clock_in_status === 'late'));
                    @endphp
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/8 dark:border-white/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-[9px] {{ $isPresent ? 'bg-[#34C759]/10 text-[#34C759]' : ($isLate ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-[#007AFF]/10 text-[#007AFF]') }} flex items-center justify-center">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-[13px] font-bold text-slate-900 dark:text-white">
                                    {{ $row->date ? $row->date->translatedFormat('d M Y') : '-' }}
                                </span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold {{ $isPresent ? 'bg-[#34C759]/15 text-[#34C759]' : ($isLate ? 'bg-[#FF9500]/15 text-[#FF9500]' : 'bg-[#007AFF]/15 text-[#007AFF]') }}">
                                {{ ucfirst($row->status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 text-xs pt-1 border-t border-black/5 dark:border-white/5">
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.col_clock_in') }}</span>
                                <span class="text-[12.5px] font-bold font-mono text-black dark:text-white tabular-nums">
                                    {{ $row->clock_in_at ? $row->clock_in_at->format('H:i') . ' WIB' : '-' }}
                                </span>
                            </div>
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.clock_out_short') }}</span>
                                <span class="text-[12.5px] font-bold font-mono text-black dark:text-white tabular-nums">
                                    {{ $row->clock_out_at ? $row->clock_out_at->format('H:i') . ' WIB' : '-' }}
                                </span>
                            </div>
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.col_work_duration') }}</span>
                                <span class="text-[12.5px] font-bold font-mono text-black dark:text-white tabular-nums">
                                    {{ $row->work_duration_minutes ? __('portal.hours_suffix', ['count' => round($row->work_duration_minutes / 60, 1)]) : '-' }}
                                </span>
                            </div>
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('portal.col_overtime') }}</span>
                                <span class="text-[12.5px] font-bold font-mono {{ $row->overtime_minutes > 0 ? 'text-[#AF52DE]' : 'text-black/40 dark:text-white/40' }} tabular-nums">
                                    {{ $row->overtime_minutes > 0 ? __('portal.hours_suffix', ['count' => round($row->overtime_minutes / 60, 1)]) : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400">
                        <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-1.5 text-slate-300 dark:text-slate-600"></i>
                        <p class="text-xs">{{ __('portal.empty_attendance_logs') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ======================================================== --}}
    {{-- MODAL 1: 1-DIRECTION FRONTAL FACE BIOMETRIC + LIVE MAP   --}}
    {{-- ======================================================== --}}
    <div x-show="showFaceAttendanceModal" x-transition.opacity class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/80 backdrop-blur-lg" style="display: none;">
        <div @click.outside="closeAttendanceModal()" class="w-full sm:max-w-lg bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[28px] border-t sm:border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.4)] overflow-hidden flex flex-col max-h-[92dvh] sm:max-h-[90vh]">
            
            <!-- Mobile Sheet Drag Handle Indicator -->
            <div class="sm:hidden w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mt-2.5 mb-0.5 shrink-0"></div>

            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-[12px] flex items-center justify-center shrink-0"
                        :class="attendanceStep === 'map' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#34C759]/10 text-[#34C759]'">
                        <i :data-lucide="attendanceStep === 'map' ? 'map-pin' : 'scan-face'" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-[15px] font-bold text-black dark:text-white" x-text="attendanceStep === 'map' ? '{{ __('portal.face_modal_title_map') }}' : '{{ __('portal.face_modal_title') }}'"></h4>
                        <div class="flex items-center gap-2 text-[11.5px] font-semibold text-black/55 dark:text-white/55">
                            <span x-text="attendanceActionType === 'in' ? '{{ __('portal.clock_in_title') }}' : '{{ __('portal.clock_out_title') }}'"></span>
                            <span>&bull;</span>
                            <span x-text="attendanceStep === 'map' ? '{{ __('portal.face_step_map_sub') }}' : '{{ __('portal.face_step_camera_sub') }}'"></span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="closeAttendanceModal()" aria-label="{{ __('common.close') }}" class="w-10 h-10 min-w-[40px] min-h-[40px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 active:scale-95 transition cursor-pointer">
                    <i data-lucide="x" class="w-4.5 h-4.5"></i>
                </button>
            </div>

            <!-- Modal Content Body (Scrollable) -->
            <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 overscroll-contain">
                
                <!-- ================= STEP 1: 1-DIRECTION FRONTAL FACE CAMERA ================= -->
                <div x-show="attendanceStep === 'camera'" class="space-y-4">
                    
                    <!-- Frontal Guide Banner -->
                    <div class="p-3 rounded-[14px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12px] font-medium text-black/80 dark:text-white/80 flex items-center gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                        <span>{{ __('portal.face_frontal_guide') }}</span>
                    </div>

                    <!-- Video HUD Container (Square 1:1 Aspect Ratio) -->
                    <div class="relative w-full aspect-square max-w-[320px] mx-auto rounded-[22px] overflow-hidden bg-black flex items-center justify-center shadow-xl border-2 transition-colors duration-300"
                        :class="faceVerifiedSuccess ? 'border-[#34C759]' : (faceSteady ? 'border-[#007AFF]' : (faceDetected ? 'border-[#FF9500]' : 'border-[#FF3B30]/60'))">
                        
                        <video id="portalLivenessVideo" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                        
                        <!-- Oval Face Frame Guide Overlay -->
                        <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-3.5">
                            
                            <!-- Top status badge -->
                            <div class="px-3.5 py-1.5 rounded-full bg-black/75 backdrop-blur-md border border-white/20 text-white text-[11.5px] font-bold flex items-center gap-2 shadow-lg">
                                <span class="w-2 h-2 rounded-full"
                                    :class="faceVerifiedSuccess ? 'bg-[#34C759]' : (faceSteady ? 'bg-[#007AFF] animate-pulse' : (faceDetected ? 'bg-[#FF9500]' : 'bg-[#FF3B30] animate-ping'))"></span>
                                <span x-text="liveDetectionStatus"></span>
                            </div>

                            <!-- Center Oval Target Ring -->
                            <div class="relative w-[72%] h-[82%] rounded-[50%] border-4 transition-all duration-300 flex items-center justify-center"
                                :class="faceVerifiedSuccess ? 'border-[#34C759] scale-105 bg-[#34C759]/20 shadow-[0_0_30px_rgba(52,199,89,0.5)]' : (faceSteady ? 'border-[#007AFF] scale-102 shadow-[0_0_24px_rgba(0,122,255,0.4)]' : (faceDetected ? 'border-[#FF9500] border-dashed shadow-[0_0_0_9999px_rgba(0,0,0,0.45)]' : 'border-[#FF3B30] border-dashed shadow-[0_0_0_9999px_rgba(0,0,0,0.6)]'))">
                                
                                <template x-if="faceVerifiedSuccess">
                                    <div class="w-16 h-16 rounded-full bg-[#34C759] flex items-center justify-center mx-auto animate-pulse shadow-xl shadow-[#34C759]/50">
                                        <i data-lucide="check" class="w-9 h-9 text-white"></i>
                                    </div>
                                </template>
                            </div>

                            <!-- Bottom Helper Indicator -->
                            <div class="w-full text-center py-2 px-3 rounded-[12px] bg-black/80 backdrop-blur-md border border-white/20 shadow-md">
                                <p class="text-[12px] font-bold text-white tracking-wide" x-text="liveGuideText">
                                    {{ __('portal.face_guide_frontal') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Stability Progress Indicator -->
                    <div class="space-y-1.5 max-w-[320px] mx-auto">
                        <div class="flex justify-between items-center text-[11px] font-bold text-black/70 dark:text-white/70">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="scan" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>{{ __('portal.face_stability_label') }}</span>
                            </span>
                            <span class="tabular-nums font-mono text-[#007AFF]" x-text="stabilityProgress + '%'"></span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-black/10 dark:bg-white/10 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[#007AFF] to-[#34C759] transition-all duration-150" :style="'width: ' + stabilityProgress + '%'"></div>
                        </div>
                    </div>

                    <!-- Error Alert Display -->
                    <template x-if="attendanceErrorMessage">
                        <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12px] font-semibold text-[#FF3B30] flex items-start gap-2 animate-fadeIn">
                            <i data-lucide="alert-circle" class="w-4.5 h-4.5 shrink-0 mt-0.5"></i>
                            <div class="flex-1">
                                <p x-text="attendanceErrorMessage"></p>
                                <button type="button" @click="retryFaceCapture()" class="mt-2 px-3 py-1 bg-[#FF3B30] text-white rounded-[8px] text-[11px] font-bold hover:bg-[#E0352B] transition">
                                    {{ __('portal.face_retry_btn') }}
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Manual Snap Button & Spinner -->
                    <div class="flex flex-col items-center gap-2 pt-1">
                        <button type="button"
                            @click="captureAndVerifyFace()"
                            :disabled="isVerifyingFace || faceVerifiedSuccess"
                            class="w-full max-w-[320px] h-12 rounded-[14px] bg-[#34C759] hover:bg-[#2FB34F] active:scale-[0.98] text-white text-[13.5px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_10px_rgba(52,199,89,0.3)] transition cursor-pointer">
                            <template x-if="isVerifyingFace">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                    <span>{{ __('portal.face_verifying') }}</span>
                                </div>
                            </template>
                            <template x-if="!isVerifyingFace">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="aperture" class="w-4.5 h-4.5"></i>
                                    <span>{{ __('portal.face_btn_capture') }}</span>
                                </div>
                            </template>
                        </button>
                        <p class="text-[11px] text-black/50 dark:text-white/50 text-center">{{ __('portal.face_privacy_notice') }}</p>
                    </div>
                </div>


                <!-- ================= STEP 2: GEOLOCATION LIVE MAP & GEOFENCE ================= -->
                <div x-show="attendanceStep === 'map'" class="space-y-3.5 animate-fadeIn">
                    
                    <!-- Biometric Match Status Pill -->
                    <div class="p-3 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <template x-if="faceCapturedFrame">
                                <img :src="faceCapturedFrame" class="w-10 h-10 rounded-[10px] object-cover border-2 border-[#34C759] shadow-xs shrink-0">
                            </template>
                            <div class="min-w-0">
                                <span class="text-[12px] font-bold text-[#34C759] flex items-center gap-1.5 truncate">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                                    <span>{{ __('portal.face_verified_success') }}</span>
                                </span>
                                <span class="text-[10.5px] font-semibold text-black/60 dark:text-white/60 block truncate"
                                    x-text="'{{ __('portal.face_similarity_threshold') }}'.replace(':score', faceVerificationResult?.similarity_percent || 85).replace(':threshold', faceVerificationResult?.threshold_percent || 55)"></span>
                            </div>
                        </div>
                        <button type="button" @click="retryFaceCapture()" class="text-[11px] font-bold text-[#007AFF] hover:underline cursor-pointer shrink-0">
                            {{ __('portal.face_change_photo') }}
                        </button>
                    </div>

                    <!-- Live Leaflet Map Container -->
                    <div class="relative w-full h-[200px] sm:h-[260px] rounded-[18px] overflow-hidden border border-black/10 dark:border-white/10 shadow-xs">
                        <div id="portalAttendanceLeafletMap" class="w-full h-full" style="min-height: 200px;"></div>
                        
                        <!-- GPS Fetching Overlay if loading -->
                        <div x-show="isLoadingLocation" class="absolute inset-0 bg-black/60 backdrop-blur-xs flex flex-col items-center justify-center text-white space-y-2 z-20">
                            <i data-lucide="loader-2" class="w-7 h-7 animate-spin text-[#007AFF]"></i>
                            <span class="text-xs font-bold">{{ __('portal.map_fetching_gps') }}</span>
                        </div>
                    </div>

                    <!-- Distance & Geofence Metric Cards (Bento Apple HIG Grid) -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 space-y-0.5">
                            <span class="text-[10px] uppercase font-bold text-black/45 dark:text-white/45 block">{{ __('portal.map_distance_label') }}</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-base font-black text-black dark:text-white tabular-nums" x-text="liveDistanceMeters !== null ? liveDistanceMeters : '--'"></span>
                                <span class="text-xs font-bold text-black/60 dark:text-white/60">{{ __('hrm.unit_meters') }}</span>
                            </div>
                            <span class="text-[9.5px] text-black/50 dark:text-white/50 block" x-text="'{{ __('portal.map_max_radius') }}'.replace(':meters', allowedRadius)"></span>
                        </div>

                        <div class="p-3 rounded-[14px] border space-y-0.5"
                            :class="isInsideGeofence ? 'bg-[#34C759]/10 border-[#34C759]/25 text-[#34C759]' : 'bg-[#FF3B30]/10 border-[#FF3B30]/25 text-[#FF3B30]'">
                            <span class="text-[10px] uppercase font-bold block" :class="isInsideGeofence ? 'text-[#34C759]' : 'text-[#FF3B30]'">{{ __('portal.map_status_geofence') }}</span>
                            <div class="flex items-center gap-1.5">
                                <i :data-lucide="isInsideGeofence ? 'shield-check' : 'alert-triangle'" class="w-3.5 h-3.5 shrink-0"></i>
                                <span class="text-[11.5px] font-black truncate" x-text="isInsideGeofence ? '{{ __('portal.map_status_inside') }}' : '{{ __('portal.map_status_outside') }}'"></span>
                            </div>
                            <span class="text-[9.5px] opacity-80 block" x-text="'{{ __('portal.map_accuracy_label') }}'.replace(':meters', currentGpsAccuracy ? Math.round(currentGpsAccuracy) : 10)"></span>
                        </div>
                    </div>

                    <!-- Outside Radius Notice (if any) -->
                    <template x-if="!isInsideGeofence && !activeException">
                        <div class="p-3 rounded-[12px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[11px] text-[#FF9500] font-medium flex items-start gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span>{{ __('portal.map_outside_warning', ['distance' => ':dist', 'allowed' => ':allow']) }}</span>
                        </div>
                    </template>

                    <!-- Error alert on submit -->
                    <template x-if="attendanceErrorMessage">
                        <div class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[11.5px] font-semibold text-[#FF3B30] flex items-start gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span x-text="attendanceErrorMessage"></span>
                        </div>
                    </template>

                </div>

            </div>

            <!-- Sticky Bottom Confirmation Action Bar (Thumb Zone Ergonomics) -->
            <div x-show="attendanceStep === 'map'" class="p-3.5 sm:p-4 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md border-t border-black/5 dark:border-white/10 shrink-0">
                <button type="button"
                    @click="submitFinalAttendance()"
                    :disabled="isSubmittingAttendance"
                    class="w-full h-12 rounded-[14px] text-white text-[13.5px] font-bold flex items-center justify-center gap-2 shadow-lg transition active:scale-[0.98] cursor-pointer"
                    :class="attendanceActionType === 'in' ? 'bg-[#34C759] hover:bg-[#2FB34F] shadow-[#34C759]/25' : 'bg-[#FF9500] hover:bg-[#E08500] shadow-[#FF9500]/25'">
                    <template x-if="isSubmittingAttendance">
                        <div class="flex items-center gap-2">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                            <span>{{ __('portal.map_submitting') }}</span>
                        </div>
                    </template>
                    <template x-if="!isSubmittingAttendance">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4.5 h-4.5"></i>
                            <span x-text="attendanceActionType === 'in' ? '{{ __('portal.map_btn_confirm_in') }}' : '{{ __('portal.map_btn_confirm_out') }}'"></span>
                        </div>
                    </template>
                </button>
            </div>

        </div>
    </div>


    {{-- ======================================================== --}}
    {{-- MODAL 2: REGISTRASI BIOMETRIK WAJAH KARYAWAN             --}}
    {{-- ======================================================== --}}
    <div x-show="showFaceRegisterModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" style="display: none;">
        <div @click.outside="stopFaceRegCamera(); showFaceRegisterModal = false" class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_28px_56px_rgba(0,0,0,0.3)] overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="scan-face" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('portal.modal_face_reg_title') }}</h3>
                        <p class="text-[12px] text-black/60 dark:text-white/60">
                            {{ __('portal.staff_label', ['name' => $user->name]) }}
                        </p>
                    </div>
                </div>
                <button type="button" @click="stopFaceRegCamera(); showFaceRegisterModal = false" aria-label="{{ __('common.close') }}" class="w-10 h-10 min-w-[40px] min-h-[40px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 active:scale-95 transition cursor-pointer">
                    <i data-lucide="x" class="w-4.5 h-4.5"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 overflow-y-auto">
                <!-- Mode Switcher -->
                <div class="flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08]">
                    <button type="button" @click="faceRegMode = 'camera'; startFaceRegCamera()"
                        :class="faceRegMode === 'camera' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 font-semibold'"
                        class="flex-1 py-2 text-[12.5px] rounded-[9px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <span>{{ __('portal.modal_face_reg_mode_webcam') }}</span>
                    </button>
                    <button type="button" @click="faceRegMode = 'upload'; stopFaceRegCamera()"
                        :class="faceRegMode === 'upload' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 font-semibold'"
                        class="flex-1 py-2 text-[12.5px] rounded-[9px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>{{ __('portal.modal_face_reg_mode_upload') }}</span>
                    </button>
                </div>

                <!-- Webcam Capture View -->
                <template x-if="faceRegMode === 'camera'">
                    <div class="space-y-3">
                        <div class="relative w-full aspect-square max-w-[320px] mx-auto rounded-[20px] overflow-hidden bg-black flex items-center justify-center border-2 border-[#34C759]/40 shadow-inner">
                            <video id="portalFaceRegVideo" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                            
                            <!-- Oval Face Frame Guide -->
                            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                <div class="w-[72%] h-[82%] rounded-[50%] border-2 border-dashed border-white/80 shadow-[0_0_0_9999px_rgba(0,0,0,0.45)] flex items-center justify-center">
                                    <span class="text-[11px] font-bold text-white/90 bg-black/60 px-3 py-1 rounded-full uppercase tracking-wider">{{ __('portal.modal_face_reg_guide') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button type="button" @click="captureFaceRegSnapshot()"
                                class="h-11 px-6 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                                <i data-lucide="aperture" class="w-4.5 h-4.5"></i>
                                <span>{{ __('portal.modal_face_reg_btn_snap') }}</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- File Upload View -->
                <template x-if="faceRegMode === 'upload'">
                    <div class="space-y-3">
                        <label class="block w-full p-6 border-2 border-dashed border-black/15 dark:border-white/15 rounded-[18px] text-center hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                            <i data-lucide="image" class="w-8 h-8 text-[#007AFF] mx-auto mb-2"></i>
                            <span class="text-[13px] font-bold text-black dark:text-white block">{{ __('portal.modal_face_reg_upload_prompt') }}</span>
                            <span class="text-[11.5px] text-black/55 dark:text-white/55 block mt-0.5">{{ __('portal.modal_face_reg_upload_hint') }}</span>
                            <input type="file" accept="image/*" @change="handleFaceRegUpload($event)" class="hidden">
                        </label>
                    </div>
                </template>

                <!-- Photo Preview Section -->
                <template x-if="faceRegPreview">
                    <div class="p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center gap-3">
                        <img :src="faceRegPreview" class="w-14 h-14 rounded-[12px] object-cover border border-[#34C759]/40 shadow-xs">
                        <div class="min-w-0 flex-1">
                            <span class="text-[12.5px] font-bold text-[#34C759] flex items-center gap-1">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>{{ __('portal.modal_face_reg_ready') }}</span>
                            </span>
                            <p class="text-[11px] text-black/60 dark:text-white/60">{{ __('portal.modal_face_reg_ready_desc') }}</p>
                        </div>
                    </div>
                </template>

                <!-- Submit Button -->
                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="stopFaceRegCamera(); showFaceRegisterModal = false"
                        class="h-11 sm:h-10 px-4.5 rounded-[11px] text-[14px] sm:text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('portal.action_cancel') }}
                    </button>
                    <button type="button" @click="submitFaceRegistration()" :disabled="!faceRegPhotoData || isSubmittingFaceReg"
                        :class="faceRegPhotoData && !isSubmittingFaceReg ? 'bg-[#34C759] hover:bg-[#2FB34F] text-white cursor-pointer shadow-[0_2px_8px_rgba(52,199,89,0.3)]' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40 cursor-not-allowed'"
                        class="h-11 sm:h-10 px-5 rounded-[11px] text-[14px] sm:text-[13px] font-bold transition active:scale-[0.98] flex items-center gap-2">
                        <template x-if="isSubmittingFaceReg">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        </template>
                        <span>{{ __('portal.modal_face_reg_btn_save') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL 3: PENGAJUAN KOREKSI ABSENSI                       --}}
    {{-- ======================================================== --}}
    <div x-show="showCorrectionModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
        style="display: none;">
        <div class="w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 shadow-2xl space-y-4 text-left"
            @click.away="showCorrectionModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="file-edit" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ __('portal.modal_correction_title') }}</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">{{ __('portal.modal_correction_subtitle') }}</p>
                    </div>
                </div>
                <button type="button" @click="showCorrectionModal = false" aria-label="{{ __('common.close') }}" class="w-10 h-10 min-w-[40px] min-h-[40px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 active:scale-95 transition cursor-pointer">
                    <i data-lucide="x" class="w-4.5 h-4.5"></i>
                </button>
            </div>

            <form action="{{ route('portal.corrections.store') }}" method="POST" enctype="multipart/form-data"
                @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;"
                class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('portal.modal_correction_field_date') }} *</label>
                    <input type="date" name="target_date" required max="{{ now()->toDateString() }}"
                        class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('portal.modal_correction_field_type') }} *</label>
                        <select name="correction_type" required
                            class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white">
                            <option value="both">{{ __('portal.corr_type_both') }}</option>
                            <option value="clock_in">{{ __('portal.corr_type_in') }}</option>
                            <option value="clock_out">{{ __('portal.corr_type_out') }}</option>
                            <option value="permit">{{ __('portal.corr_type_permit') }}</option>
                            <option value="leave">{{ __('portal.corr_type_leave') }}</option>
                            <option value="sick">{{ __('portal.corr_type_sick') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('portal.modal_correction_field_status') }}</label>
                        <select name="proposed_status"
                            class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white">
                            <option value="present">{{ __('portal.metric_on_time') }}</option>
                            <option value="late">{{ __('portal.metric_late') }}</option>
                            <option value="permit">{{ __('portal.corr_type_permit') }}</option>
                            <option value="leave">{{ __('portal.corr_type_leave') }}</option>
                            <option value="sick">{{ __('portal.sick') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('portal.modal_correction_field_clock_in') }}</label>
                        <input type="time" name="proposed_clock_in"
                            class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('portal.modal_correction_field_clock_out') }}</label>
                        <input type="time" name="proposed_clock_out"
                            class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('portal.modal_correction_field_reason') }} *</label>
                    <textarea name="reason" rows="3" required placeholder="{{ __('portal.correction_reason_placeholder') }}"
                        class="w-full p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('portal.modal_correction_field_attachment') }}</label>
                    <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full text-[16px] sm:text-xs text-black/60 dark:text-white/60 file:mr-3 file:py-2.5 file:px-3.5 file:rounded-[8px] file:border-0 file:text-[13px] sm:file:text-xs file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF]">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showCorrectionModal = false"
                        class="h-11 sm:h-10 px-4 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-[13px] sm:text-xs font-semibold text-black dark:text-white cursor-pointer">
                        {{ __('portal.action_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        :class="isSubmitting ? 'opacity-60 cursor-not-allowed' : ''"
                        class="h-11 sm:h-10 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-[13px] sm:text-xs font-semibold text-white shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                        <template x-if="!isSubmitting">
                            <span>{{ __('portal.modal_correction_btn_submit') }}</span>
                        </template>
                        <template x-if="isSubmitting">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('portal.sending_state') }}</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL 4: FEEDBACK STATUS ABSENSI & JADWAL KERJA (HIG)    --}}
    {{-- ======================================================== --}}
    <div x-show="showFeedbackModal && clockFeedback" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 shadow-2xl space-y-5 text-left relative overflow-hidden"
            @click.away="showFeedbackModal = false">
            
            {{-- Top Accent Bar --}}
            <div class="absolute top-0 inset-x-0 h-1.5"
                :class="{
                    'bg-[#FF3B30]': clockFeedback?.status === 'late',
                    'bg-[#34C759]': clockFeedback?.status === 'on_time',
                    'bg-[#007AFF]': clockFeedback?.status === 'early',
                    'bg-slate-400': clockFeedback?.status === 'free'
                }"></div>

            {{-- Header --}}
            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] flex items-center justify-center"
                        :class="{
                            'bg-[#FF3B30]/10 text-[#FF3B30]': clockFeedback?.status === 'late',
                            'bg-[#34C759]/10 text-[#34C759]': clockFeedback?.status === 'on_time',
                            'bg-[#007AFF]/10 text-[#007AFF]': clockFeedback?.status === 'early',
                            'bg-slate-400/10 text-slate-500': clockFeedback?.status === 'free'
                        }">
                        <template x-if="clockFeedback?.status === 'late'">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </template>
                        <template x-if="clockFeedback?.status !== 'late'">
                            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                        </template>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-black dark:text-white">
                            Presensi Berhasil Dicatat
                        </h3>
                        <p class="text-[11px] font-medium text-black/55 dark:text-white/55">
                            Status kepatuhan terhadap jadwal kerja
                        </p>
                    </div>
                </div>
                <button type="button" @click="showFeedbackModal = false"
                    aria-label="{{ __('common.close') }}"
                    class="p-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 transition cursor-pointer text-black/40 dark:text-white/40">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Main Clock In Info Card --}}
            <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-center space-y-2">
                <div class="text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40">
                    Waktu Tercatat
                </div>
                <div class="text-4xl font-extrabold tracking-tight text-black dark:text-white font-mono tabular-nums">
                    <span x-text="clockFeedback?.clock_in_time || clockFeedback?.clock_out_time || '--:--'"></span>
                    <span class="text-sm font-bold text-[#007AFF]" x-text="clockFeedback?.tz_abbr || 'WIB'"></span>
                </div>
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold tracking-wide uppercase"
                        :class="{
                            'bg-[#FF3B30]/15 text-[#FF3B30] border border-[#FF3B30]/30': clockFeedback?.status === 'late',
                            'bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30': clockFeedback?.status === 'on_time',
                            'bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30': clockFeedback?.status === 'early',
                            'bg-slate-400/15 text-slate-500 border border-slate-400/30': clockFeedback?.status === 'free'
                        }"
                        x-text="clockFeedback?.status_label || 'Tepat Waktu'">
                    </span>
                </div>
            </div>

            {{-- Schedule Details List --}}
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between py-2 border-b border-black/5 dark:border-white/5">
                    <span class="font-medium text-black/60 dark:text-white/60">Shift Kerja</span>
                    <span class="font-bold text-black dark:text-white" x-text="clockFeedback?.shift_name || '-'"></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-black/5 dark:border-white/5">
                    <span class="font-medium text-black/60 dark:text-white/60">Jadwal Masuk</span>
                    <span class="font-bold text-black dark:text-white font-mono tabular-nums"
                        x-text="clockFeedback?.scheduled_start ? (clockFeedback.scheduled_start + ' ' + (clockFeedback?.tz_abbr || 'WIB')) : 'Bebas / Tanpa Jadwal'"></span>
                </div>
                <template x-if="clockFeedback?.late_minutes > 0">
                    <div class="flex items-center justify-between py-2 border-b border-black/5 dark:border-white/5 text-[#FF3B30]">
                        <span class="font-bold">Keterlambatan</span>
                        <span class="font-extrabold font-mono tabular-nums" x-text="clockFeedback.late_minutes + ' menit'"></span>
                    </div>
                </template>
                <template x-if="clockFeedback?.early_in_minutes > 0">
                    <div class="flex items-center justify-between py-2 border-b border-black/5 dark:border-white/5 text-[#007AFF]">
                        <span class="font-bold">Lebih Awal</span>
                        <span class="font-extrabold font-mono tabular-nums" x-text="clockFeedback.early_in_minutes + ' menit'"></span>
                    </div>
                </template>
                <template x-if="clockFeedback?.early_out_minutes > 0">
                    <div class="flex items-center justify-between py-2 border-b border-black/5 dark:border-white/5 text-[#FF9500]">
                        <span class="font-bold">Pulang Lebih Awal</span>
                        <span class="font-extrabold font-mono tabular-nums" x-text="clockFeedback.early_out_minutes + ' menit'"></span>
                    </div>
                </template>
                <template x-if="clockFeedback?.grace_period_minutes > 0">
                    <div class="flex items-center justify-between py-1 text-[11px] text-black/50 dark:text-white/50">
                        <span>Toleransi Keterlambatan</span>
                        <span class="font-medium font-mono" x-text="clockFeedback.grace_period_minutes + ' menit'"></span>
                    </div>
                </template>
            </div>

            {{-- Action Button --}}
            <div class="pt-2">
                <button type="button" @click="showFeedbackModal = false"
                    class="w-full h-11 rounded-[12px] bg-black dark:bg-white text-white dark:text-black font-bold text-xs tracking-wide transition active:scale-[0.98] cursor-pointer shadow-sm">
                    Mengerti & Lanjutkan
                </button>
            </div>
        </div>
    </div>

</div>

{{-- Alpine.js Interactive Portal Script --}}
<script>
function portalAttendance(config) {
    return {
        activeTab: config.activeTab || 'attendance',
        attendance: config.todayAttendance,
        activeException: config.activeException,
        cityName: config.cityName || 'Jakarta',
        officeName: config.officeName || 'Kantor Utama',
        defaultLat: parseFloat(config.defaultLat) || -6.2088,
        defaultLng: parseFloat(config.defaultLng) || 106.8456,
        allowedRadius: parseInt(config.allowedRadius) || 50,
        clockInRoute: config.clockInRoute,
        clockOutRoute: config.clockOutRoute,
        faceRegisterRoute: config.faceRegisterRoute,
        faceVerifyRoute: config.faceVerifyRoute,
        hasFaceRegistered: config.hasFaceRegistered || false,
        timezone: config.timezone || 'Asia/Jakarta',
        tzAbbr: config.tzAbbr || 'WIB',
        csrfToken: config.csrfToken,

        timeString: '',
        dateString: '',
        liveDurationText: '00:00:00',
        showCorrectionModal: false,
        showFaceRegisterModal: false,
        showFaceAttendanceModal: false,
        showFeedbackModal: false,
        clockFeedback: null,

        // Face Registration
        faceRegMode: 'camera',
        faceRegPhotoData: null,
        faceRegPreview: null,
        faceRegStream: null,
        isSubmittingFaceReg: false,

        // 1-Direction Frontal Face Attendance & Geolocation Live Map
        attendanceActionType: 'in',
        attendanceStep: 'camera', // 'camera' | 'map'
        isSubmittingAttendance: false,
        isVerifyingFace: false,
        faceVerifiedSuccess: false,
        faceVerificationResult: null,
        faceSteady: false,
        faceDetected: false,
        currentHoldMs: 0,
        stabilityProgress: 0,
        liveDetectionStatus: 'Mempersiapkan kamera...',
        liveGuideText: 'Arahkan wajah tegak lurus ke kamera',
        attendanceErrorMessage: '',
        liveCameraStream: null,
        isDetectingPose: false,
        faceCapturedFrame: null,

        // Geolocation & Interactive Leaflet Map State
        isLoadingLocation: false,
        currentUserLat: null,
        currentUserLng: null,
        currentGpsAccuracy: null,
        liveDistanceMeters: null,
        isInsideGeofence: false,
        leafletMap: null,

        i18n: config.i18n || {},

        weather: {
            temp: null,
            condition: (config.i18n?.weatherLoading || 'Memuat data cuaca...'),
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

        playChime(freq = 587.33, duration = 0.15) {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, ctx.currentTime);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + duration);
            } catch (e) {}
        },

        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        updateClock() {
            const now = new Date();
            this.timeString = now.toLocaleTimeString('en-GB', { timeZone: this.timezone || 'Asia/Jakarta', hour12: false });
            this.dateString = now.toLocaleDateString('id-ID', {
                timeZone: this.timezone || 'Asia/Jakarta',
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            if (this.attendance && this.attendance.clock_in_at && !this.attendance.clock_out_at) {
                const inTime = new Date(this.attendance.clock_in_at);
                const diffMs = Math.max(0, now - inTime);
                const diffSec = Math.floor(diffMs / 1000);
                const h = String(Math.floor(diffSec / 3600)).padStart(2, '0');
                const m = String(Math.floor((diffSec % 3600) / 60)).padStart(2, '0');
                const s = String(diffSec % 60).padStart(2, '0');
                this.liveDurationText = `${h}:${m}:${s}`;
            }
        },

        fetchWeather() {
            fetch(`https://api.open-meteo.com/v1/forecast?latitude=${this.defaultLat}&longitude=${this.defaultLng}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code&timezone=Asia%2FJakarta`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.current) {
                        this.weather.temp = Math.round(data.current.temperature_2m);
                        this.weather.humidity = data.current.relative_humidity_2m;
                        this.weather.wind = Math.round(data.current.wind_speed_10m);
                        const code = data.current.weather_code;
                        if (code === 0) {
                            this.weather.condition = 'Cerah';
                            this.weather.icon = 'sun';
                        } else if (code <= 3) {
                            this.weather.condition = 'Cerah Berawan';
                            this.weather.icon = 'cloud-sun';
                        } else if (code <= 65) {
                            this.weather.condition = 'Hujan Ringan';
                            this.weather.icon = 'cloud-rain';
                        } else {
                            this.weather.condition = 'Berawan';
                            this.weather.icon = 'cloud';
                        }
                    }
                })
                .catch(() => {
                    this.weather.condition = 'Jakarta & Sekitarnya';
                })
                .finally(() => {
                    if (window.lucide) this.$nextTick(() => window.lucide.createIcons());
                });
        },

        get shiftStatusClass() {
            if (this.attendance && this.attendance.clock_out_at) return 'bg-[#5856D6]/10 text-[#5856D6]';
            if (this.attendance && this.attendance.clock_in_at) return 'bg-[#34C759]/10 text-[#34C759]';
            return 'bg-[#FF9500]/10 text-[#FF9500]';
        },

        get shiftDotClass() {
            if (this.attendance && this.attendance.clock_out_at) return 'bg-[#5856D6]';
            if (this.attendance && this.attendance.clock_in_at) return 'bg-[#34C759] animate-pulse';
            return 'bg-[#FF9500]';
        },

        get shiftStatusText() {
            if (this.attendance && this.attendance.clock_out_at) return 'Sudah Selesai Bertugas';
            if (this.attendance && this.attendance.clock_in_at) return 'Sedang Bertugas';
            return 'Belum Absen Masuk';
        },

        get todayStatusTitle() {
            if (this.attendance && this.attendance.clock_out_at) return 'Selesai Pulang';
            if (this.attendance && this.attendance.clock_in_at) return 'Sedang Bertugas';
            return 'Belum Absen';
        },

        formatTime(isoString) {
            if (!isoString) return '--:--';
            const d = new Date(isoString);
            return d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
        },

        // --- FACE REGISTRATION LOGIC ---
        openFaceRegisterModal() {
            this.faceRegPhotoData = null;
            this.faceRegPreview = null;
            this.faceRegMode = 'camera';
            this.showFaceRegisterModal = true;
            this.$nextTick(() => {
                this.startFaceRegCamera();
                if (window.lucide) window.lucide.createIcons();
            });
        },

        startFaceRegCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;
            navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 640 }, facingMode: 'user' }
            }).then(stream => {
                this.faceRegStream = stream;
                const video = document.getElementById('portalFaceRegVideo');
                if (video) {
                    video.srcObject = stream;
                    video.play();
                }
            }).catch(err => {
                console.error('Kamera registrasi gagal:', err);
            });
        },

        stopFaceRegCamera() {
            if (this.faceRegStream) {
                this.faceRegStream.getTracks().forEach(t => t.stop());
                this.faceRegStream = null;
            }
        },

        captureFaceRegSnapshot() {
            const video = document.getElementById('portalFaceRegVideo');
            if (!video) return;
            const canvas = document.createElement('canvas');
            canvas.width = 640;
            canvas.height = 640;
            const ctx = canvas.getContext('2d');
            const vw = video.videoWidth || 640;
            const vh = video.videoHeight || 480;
            const minDim = Math.min(vw, vh);
            const sx = (vw - minDim) / 2;
            const sy = (vh - minDim) / 2;
            ctx.drawImage(video, sx, sy, minDim, minDim, 0, 0, 640, 640);
            this.faceRegPhotoData = canvas.toDataURL('image/jpeg', 0.9);
            this.faceRegPreview = this.faceRegPhotoData;
            this.stopFaceRegCamera();
        },

        handleFaceRegUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.faceRegPhotoData = e.target.result;
                this.faceRegPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        submitFaceRegistration() {
            if (!this.faceRegPhotoData || this.isSubmittingFaceReg) return;
            this.isSubmittingFaceReg = true;

            fetch(this.faceRegisterRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ photo: this.faceRegPhotoData })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal mendaftarkan biometrik wajah.');
                this.hasFaceRegistered = true;
                this.showFaceRegisterModal = false;
                this.showToast('success', data.message || 'Template biometrik wajah berhasil didaftarkan (AES-256)!');
            })
            .catch(err => {
                this.showToast('error', err.message);
            })
            .finally(() => {
                this.isSubmittingFaceReg = false;
                this.stopFaceRegCamera();
            });
        },

        // --- 1-DIRECTION FRONTAL FACE ATTENDANCE & INTERACTIVE LIVE MAP ENGINE ---
        startFaceAttendance(type = 'in') {
            if (!this.hasFaceRegistered) {
                this.showToast('error', 'Wajah belum terdaftar! Anda wajib mendaftarkan template biometrik wajah terlebih dahulu sebelum dapat melakukan presensi.');
                this.openFaceRegisterModal();
                return;
            }

            this.attendanceActionType = type;
            this.attendanceStep = 'camera';
            this.faceVerifiedSuccess = false;
            this.faceVerificationResult = null;
            this.isVerifyingFace = false;
            this.faceDetected = false;
            this.faceSteady = false;
            this.currentHoldMs = 0;
            this.stabilityProgress = 0;
            this.faceCapturedFrame = null;
            this.attendanceErrorMessage = '';
            this.liveDetectionStatus = 'Membuka kamera video...';
            this.liveGuideText = 'Arahkan wajah tegak lurus ke kamera';
            this.showFaceAttendanceModal = true;

            this.$nextTick(() => {
                this.startAttendanceCamera();
                if (window.lucide) window.lucide.createIcons();
            });
        },

        startAttendanceCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.attendanceErrorMessage = 'Perangkat tidak mendukung akses kamera video.';
                return;
            }

            navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 640 },
                    facingMode: 'user'
                }
            }).then(stream => {
                this.liveCameraStream = stream;
                const video = document.getElementById('portalLivenessVideo');
                if (video) {
                    video.srcObject = stream;
                    video.onloadedmetadata = () => {
                        video.play();
                        this.runFrontalFaceLoop();
                    };
                }
            }).catch(err => {
                this.attendanceErrorMessage = 'Izin kamera ditolak atau kamera tidak ditemukan: ' + err.message;
            });
        },

        stopAttendanceCamera() {
            if (this.liveCameraStream) {
                this.liveCameraStream.getTracks().forEach(t => t.stop());
                this.liveCameraStream = null;
            }
            this.isDetectingPose = false;
        },

        closeAttendanceModal() {
            this.stopAttendanceCamera();
            if (this.leafletMap) {
                this.leafletMap.remove();
                this.leafletMap = null;
            }
            this.showFaceAttendanceModal = false;
        },

        runFrontalFaceLoop() {
            this.isDetectingPose = true;
            const video = document.getElementById('portalLivenessVideo');
            if (!video) return;

            const sampleCanvas = document.createElement('canvas');
            const sampleWidth = 120;
            const sampleHeight = 120;
            sampleCanvas.width = sampleWidth;
            sampleCanvas.height = sampleHeight;
            const sampleCtx = sampleCanvas.getContext('2d', { willReadFrequently: true });

            let lastTime = performance.now();

            const processFrame = (now) => {
                if (!this.showFaceAttendanceModal || this.attendanceStep !== 'camera' || !this.isDetectingPose || !video) return;

                const deltaMs = Math.min(100, Math.max(10, now - lastTime));
                lastTime = now;

                if (video.readyState >= video.HAVE_CURRENT_DATA) {
                    sampleCtx.drawImage(video, 0, 0, sampleWidth, sampleHeight);
                    const imgData = sampleCtx.getImageData(0, 0, sampleWidth, sampleHeight);
                    const data = imgData.data;

                    let totalSkin = 0;
                    let sumX = 0;
                    let sumY = 0;

                    const cx = sampleWidth / 2;
                    const cy = sampleHeight / 2;
                    const rx = sampleWidth * 0.36;
                    const ry = sampleHeight * 0.44;

                    // Scan pixel buffer inside central oval
                    for (let y = 0; y < sampleHeight; y += 2) {
                        for (let x = 0; x < sampleWidth; x += 2) {
                            const dx = (x - cx) / rx;
                            const dy = (y - cy) / ry;
                            if (dx * dx + dy * dy <= 1.0) {
                                const idx = (y * sampleWidth + x) * 4;
                                const r = data[idx];
                                const g = data[idx + 1];
                                const b = data[idx + 2];

                                // Skin chrominance & luminance filter
                                const isSkin = r > 45 && g > 30 && b > 20 && r > g && r > b && (r - g) > 7 && (r - b) > 9;
                                if (isSkin) {
                                    totalSkin++;
                                    sumX += x;
                                    sumY += y;
                                }
                            }
                        }
                    }

                    const maxOvalPixels = (Math.PI * rx * ry) / 4;
                    const coverage = totalSkin / Math.max(1, maxOvalPixels);

                    if (coverage < 0.10) {
                        this.faceDetected = false;
                        this.faceSteady = false;
                        this.liveDetectionStatus = 'Arahkan wajah ke dalam oval';
                        this.liveGuideText = 'Posisikan wajah tepat menghadap ke kamera';
                        this.currentHoldMs = Math.max(0, this.currentHoldMs - (deltaMs * 1.5));
                    } else if (coverage > 0.85) {
                        this.faceDetected = false;
                        this.faceSteady = false;
                        this.liveDetectionStatus = 'Wajah terlalu dekat';
                        this.liveGuideText = 'Mundurkan sedikit posisi wajah Anda';
                        this.currentHoldMs = Math.max(0, this.currentHoldMs - (deltaMs * 1.5));
                    } else {
                        this.faceDetected = true;
                        const comX = (sumX / totalSkin) / sampleWidth;  // 0.0 .. 1.0
                        const comY = (sumY / totalSkin) / sampleHeight; // 0.0 .. 1.0

                        // 1-Direction Frontal Alignment Check
                        const isFrontal = Math.abs(comX - 0.5) <= 0.09 && Math.abs(comY - 0.5) <= 0.09;

                        if (isFrontal) {
                            this.faceSteady = true;
                            this.currentHoldMs += deltaMs;
                            const reqHold = 800; // 800ms frontal stability
                            this.stabilityProgress = Math.min(100, Math.round((this.currentHoldMs / reqHold) * 100));
                            this.liveDetectionStatus = `Wajah terdeteksi frontal (${this.stabilityProgress}%)`;
                            this.liveGuideText = 'Tahan posisi diam dan stabil...';

                            if (this.currentHoldMs >= reqHold && !this.isVerifyingFace && !this.faceVerifiedSuccess) {
                                this.captureAndVerifyFace();
                                return;
                            }
                        } else {
                            this.faceSteady = false;
                            this.currentHoldMs = Math.max(0, this.currentHoldMs - (deltaMs * 1.5));
                            this.stabilityProgress = Math.min(100, Math.round((this.currentHoldMs / 800) * 100));
                            this.liveDetectionStatus = 'Posisikan tepat di tengah oval';
                            this.liveGuideText = 'Geser wajah hingga pas di tengah bingkai';
                        }
                    }
                }

                requestAnimationFrame(processFrame);
            };

            requestAnimationFrame(processFrame);
        },

        captureAndVerifyFace() {
            if (this.isVerifyingFace) return;
            const video = document.getElementById('portalLivenessVideo');
            if (!video) return;

            this.isVerifyingFace = true;
            this.isDetectingPose = false;
            this.liveDetectionStatus = 'Memverifikasi Biometrik...';
            this.liveGuideText = 'Mencocokkan dengan data terdaftar...';
            this.attendanceErrorMessage = '';

            const canvas = document.createElement('canvas');
            canvas.width = 640;
            canvas.height = 640;
            const ctx = canvas.getContext('2d');
            
            // Draw center-cropped square from video
            const vw = video.videoWidth || 640;
            const vh = video.videoHeight || 480;
            const minDim = Math.min(vw, vh);
            const sx = (vw - minDim) / 2;
            const sy = (vh - minDim) / 2;
            ctx.drawImage(video, sx, sy, minDim, minDim, 0, 0, 640, 640);

            const photoData = canvas.toDataURL('image/jpeg', 0.9);
            this.faceCapturedFrame = photoData;

            // Send to portal.face-verify
            fetch(this.faceVerifyRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ photo: photoData })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.verified) {
                    throw new Error(data.message || 'Wajah tidak cocok dengan data terdaftar.');
                }
                
                // Success!
                this.faceVerifiedSuccess = true;
                this.faceVerificationResult = data;
                this.liveDetectionStatus = `Wajah Valid (${data.similarity_percent}%)`;
                this.playChime(1046.5, 0.25);
                this.stopAttendanceCamera();

                // Immediately transition to Geolocation Live Map!
                setTimeout(() => {
                    this.attendanceStep = 'map';
                    this.fetchUserLocationAndInitMap();
                }, 400);
            })
            .catch(err => {
                this.isVerifyingFace = false;
                this.faceVerifiedSuccess = false;
                this.faceSteady = false;
                this.currentHoldMs = 0;
                this.stabilityProgress = 0;
                this.attendanceErrorMessage = err.message;
                this.liveDetectionStatus = 'Verifikasi Wajah Gagal';
                this.liveGuideText = 'Silakan coba ambil ulang foto wajah';
            });
        },

        retryFaceCapture() {
            this.attendanceStep = 'camera';
            this.faceVerifiedSuccess = false;
            this.faceVerificationResult = null;
            this.isVerifyingFace = false;
            this.faceSteady = false;
            this.currentHoldMs = 0;
            this.stabilityProgress = 0;
            this.faceCapturedFrame = null;
            this.attendanceErrorMessage = '';
            this.liveDetectionStatus = 'Membuka kamera...';
            this.liveGuideText = 'Arahkan wajah tegak lurus ke kamera';
            if (this.leafletMap) {
                this.leafletMap.remove();
                this.leafletMap = null;
            }
            this.$nextTick(() => {
                this.startAttendanceCamera();
                if (window.lucide) window.lucide.createIcons();
            });
        },

        calcDistanceMeters(lat1, lon1, lat2, lon2) {
            const R = 6371000; // Earth radius in meters
            const phi1 = lat1 * Math.PI / 180;
            const phi2 = lat2 * Math.PI / 180;
            const deltaPhi = (lat2 - lat1) * Math.PI / 180;
            const deltaLambda = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
                      Math.cos(phi1) * Math.cos(phi2) *
                      Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return Math.round(R * c);
        },

        fetchUserLocationAndInitMap() {
            this.isLoadingLocation = true;
            this.attendanceErrorMessage = '';

            const onPositionSuccess = (pos) => {
                this.isLoadingLocation = false;
                this.currentUserLat = pos.coords.latitude;
                this.currentUserLng = pos.coords.longitude;
                this.currentGpsAccuracy = pos.coords.accuracy;
                this.initAttendanceMap(pos.coords.latitude, pos.coords.longitude);
            };

            const onPositionError = (err) => {
                this.isLoadingLocation = false;
                console.warn('Geolocation error:', err);
                // Fallback to office coordinates if GPS permission issue
                this.currentUserLat = this.defaultLat;
                this.currentUserLng = this.defaultLng;
                this.currentGpsAccuracy = 20;
                this.initAttendanceMap(this.defaultLat, this.defaultLng);
            };

            if (!navigator.geolocation) {
                onPositionError(new Error('Geolocation tidak didukung browser'));
                return;
            }

            navigator.geolocation.getCurrentPosition(onPositionSuccess, onPositionError, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        },

        initAttendanceMap(userLat, userLng) {
            this.$nextTick(() => {
                const container = document.getElementById('portalAttendanceLeafletMap');
                if (!container || !window.L) return;

                if (this.leafletMap) {
                    this.leafletMap.remove();
                    this.leafletMap = null;
                }

                const officeLat = this.defaultLat;
                const officeLng = this.defaultLng;
                const uLat = (userLat !== null && userLat !== undefined) ? userLat : officeLat;
                const uLng = (userLng !== null && userLng !== undefined) ? userLng : officeLng;

                // Calculate distance
                const dist = this.calcDistanceMeters(uLat, uLng, officeLat, officeLng);
                this.liveDistanceMeters = dist;
                this.isInsideGeofence = (dist <= this.allowedRadius);

                const map = L.map('portalAttendanceLeafletMap', {
                    zoomControl: true,
                    attributionControl: false
                }).setView([uLat, uLng], 17);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(map);

                // 1. Office Location Marker
                const officeIcon = L.divIcon({
                    className: 'custom-map-marker',
                    html: `<div style="background:#007AFF;color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,122,255,0.45);border:2px solid white;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg></div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                L.marker([officeLat, officeLng], { icon: officeIcon })
                    .addTo(map)
                    .bindPopup(`<b>${this.officeName}</b><br>${window.__ ? window.__('portal.map_office_location') : 'Titik Lokasi Kantor'}`);

                // 2. Office Geofence Radius Circle
                const circleColor = this.isInsideGeofence ? '#34C759' : '#FF3B30';
                L.circle([officeLat, officeLng], {
                    radius: this.allowedRadius,
                    color: circleColor,
                    fillColor: circleColor,
                    fillOpacity: 0.15,
                    weight: 2
                }).addTo(map);

                // 3. User Live Location Marker (Pulsing Blue Dot)
                const userIcon = L.divIcon({
                    className: 'custom-map-marker',
                    html: `<div style="position:relative;width:26px;height:26px;display:flex;align-items:center;justify-content:center;"><div style="position:absolute;width:26px;height:26px;border-radius:50%;background:#007AFF;opacity:0.4;animation:ping 1.5s cubic-bezier(0,0,0.2,1) infinite;"></div><div style="width:14px;height:14px;border-radius:50%;background:#007AFF;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.35);"></div></div>`,
                    iconSize: [26, 26],
                    iconAnchor: [13, 13]
                });
                L.marker([uLat, uLng], { icon: userIcon })
                    .addTo(map)
                    .bindPopup(`<b>${window.__ ? window.__('portal.map_user_location') : 'Posisi Anda'}</b><br>${dist} m`);

                // Fit bounds to show both office and user comfortably
                try {
                    const group = new L.featureGroup([
                        L.marker([officeLat, officeLng]),
                        L.marker([uLat, uLng])
                    ]);
                    map.fitBounds(group.getBounds().pad(0.35));
                } catch(e) {}

                this.leafletMap = map;
                setTimeout(() => { map.invalidateSize(); }, 350);

                if (window.lucide) this.$nextTick(() => window.lucide.createIcons());
            });
        },

        submitFinalAttendance() {
            if (this.isSubmittingAttendance) return;
            this.isSubmittingAttendance = true;
            this.attendanceErrorMessage = '';

            const endpoint = this.attendanceActionType === 'in'
                ? this.clockInRoute
                : this.clockOutRoute;

            const payload = {
                latitude: this.currentUserLat,
                longitude: this.currentUserLng,
                accuracy: this.currentGpsAccuracy || 10,
                photo: this.faceCapturedFrame,
                face_data: this.faceCapturedFrame,
                location_id: null
            };

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || (window.__ ? window.__('messages.error') : 'Gagal mencatat presensi.'));
                this.attendance = data.attendance;
                this.closeAttendanceModal();
                if (data.feedback) {
                    this.clockFeedback = data.feedback;
                    this.showFeedbackModal = true;
                }
                this.showToast('success', data.message || (window.__ ? window.__('portal.msg_clock_in_success', { time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }) : 'Presensi berhasil dicatat!'));
                
                // Real-time Event Mutation (Zero Full-Page Reload)
                if (window.CoocaBus) {
                    window.CoocaBus.emitDataMutated('attendance', data);
                }
            })
            .catch(err => {
                this.attendanceErrorMessage = err.message;
            })
            .finally(() => {
                this.isSubmittingAttendance = false;
                if (window.lucide) this.$nextTick(() => window.lucide.createIcons());
            });
        },

        showToast(type, msg) {
            this.toast.type = type;
            this.toast.message = msg;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 6000);
            if (window.lucide) this.$nextTick(() => window.lucide.createIcons());
        }
    };
}

window.portalAttendance = portalAttendance;

document.addEventListener('alpine:init', () => {
    Alpine.data('portalAttendance', portalAttendance);
});
</script>

@endsection
