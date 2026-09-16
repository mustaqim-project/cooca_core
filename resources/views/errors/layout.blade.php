@extends('layouts.public_marketing', [
    'title' => ($code ?? '404') . ' - ' . ($title ?? 'Halaman Tidak Ditemukan') . ' | Cooca',
    'noindex' => true,
])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-lg mx-auto text-center">
            <!-- Apple Inset Error Card -->
            <div
                class="glass-card bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-2xl border border-black/5 dark:border-white/10 rounded-[28px] shadow-2xl p-8 sm:p-12 relative overflow-hidden transition-colors">

                <!-- Ambient Glow for Error Type -->
                @php
                    $isServerErr = isset($code) && (int) $code >= 500;
                    $accentColor = $isServerErr ? '#FF3B30' : '#007AFF';
                @endphp
                <div
                    class="absolute -top-24 left-1/2 -translate-x-1/2 w-48 h-48 rounded-full blur-3xl pointer-events-none opacity-20 {{ $isServerErr ? 'bg-[#FF3B30]' : 'bg-[#007AFF]' }}">
                </div>

                <!-- Apple System Icon Badge -->
                <div
                    class="relative mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl {{ $isServerErr ? 'bg-[#FF3B30]/10 text-[#FF3B30] dark:bg-[#FF453A]/15 dark:text-[#FF453A]' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF]' }} shadow-sm">
                    @if ($isServerErr)
                        <i data-lucide="server-crash" class="w-8 h-8"></i>
                    @else
                        <i data-lucide="compass" class="w-8 h-8"></i>
                    @endif
                </div>

                <!-- Error Code & Header -->
                <p
                    class="text-xs font-bold uppercase tracking-[0.2em] {{ $isServerErr ? 'text-[#FF3B30] dark:text-[#FF453A]' : 'text-[#007AFF] dark:text-[#0A84FF]' }}">
                    HTTP {{ $code ?? '404' }}
                </p>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-black dark:text-white">
                    {{ $title ?? 'Terjadi Kesalahan' }}
                </h1>
                <p class="mt-3 text-sm text-black/60 dark:text-white/60 leading-relaxed max-w-sm mx-auto">
                    {{ $message ?? 'Halaman yang Anda tuju tidak ditemukan atau sistem sedang mengalami kendala sementara.' }}
                </p>

                <!-- Apple HIG Actions -->
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <button type="button" onclick="history.back()"
                        class="w-full sm:w-auto h-11 px-6 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/90 font-semibold text-sm transition-all flex items-center justify-center gap-2">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Kembali</span>
                    </button>
                    <a href="{{ $homeUrl ?? url('/') }}"
                        class="w-full sm:w-auto h-11 px-6 rounded-[14px] glow-btn bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm transition-all flex items-center justify-center gap-2">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        <span>{{ $homeLabel ?? 'Ke Beranda' }}</span>
                    </a>
                </div>

                <!-- Tracking / Reference Badge -->
                @if (!empty($reference))
                    <div class="mt-8 pt-6 border-t border-black/5 dark:border-white/10">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/[0.03] dark:bg-white/[0.05] text-[11px] font-mono text-black/40 dark:text-white/40">
                            <i data-lucide="hash" class="w-3 h-3"></i>
                            <span>Ref ID: {{ $reference }}</span>
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
