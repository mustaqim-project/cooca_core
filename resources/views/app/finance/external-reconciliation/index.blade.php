@extends('layouts.app', ['title' => 'Lembar Rekonsiliasi & Likuiditas'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12"
    x-data="externalReconApp()"
    x-init="init()">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <x-module-header
        title="Where The Money Lives"
        subtitle="Dashboard Likuiditas & Lembar Rekonsiliasi Akun Eksternal (EDC, E-Wallet, Marketplace)">
        <a href="{{ route('finance.settlements.index') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="wallet" class="w-4 h-4"></i>
            <span>Payout Hub</span>
        </a>
        <a href="{{ route('finance.cash-bank.ledger') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            <span>Buku Kas</span>
        </a>
    </x-module-header>

    <x-module-tabs module="finance" />

    {{-- ========================================================== --}}
    {{-- PERIOD SELECTOR + LOCATION FILTER                          --}}
    {{-- ========================================================== --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-black/60 dark:text-white/60">Periode:</label>
            <input type="month" value="{{ $period }}"
                @change="window.location.href = '{{ route('finance.external-recon.index') }}?period=' + $event.target.value + (currentLocationId ? '&location_id=' + currentLocationId : '')"
                class="h-9 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs font-medium text-black dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
        </div>
        @if($locations->count() > 1)
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-black/60 dark:text-white/60">Cabang:</label>
                <select
                    @change="window.location.href = '{{ route('finance.external-recon.index') }}?period={{ $period }}&location_id=' + $event.target.value"
                    class="h-9 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs font-medium text-black dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    <option value="">Semua Cabang</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $locationId === $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION 1: WHERE THE MONEY LIVES — BENTO DASHBOARD         --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 bg-gradient-to-r from-[#007AFF]/5 to-[#5856D6]/5 dark:from-[#007AFF]/10 dark:to-[#5856D6]/10">
            <h2 class="text-[16px] font-bold text-black dark:text-white flex items-center gap-2">
                <span class="text-lg">🏦</span>
                Where The Money Lives
            </h2>
            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Posisi seluruh dana bisnis Anda — kas internal COOCA, escrow Cooca Pay, dan saldo akun eksternal periode <strong>{{ \Carbon\Carbon::parse($period . '-01')->translatedFormat('F Y') }}</strong></p>
        </div>

        <div class="p-5 sm:p-6 space-y-5">
            {{-- Grand Total Liquidity --}}
            <div class="p-5 rounded-[16px] bg-gradient-to-br from-[#007AFF]/5 via-white to-[#5856D6]/5 dark:from-[#007AFF]/10 dark:via-[#1C1C1E] dark:to-[#5856D6]/10 border border-[#007AFF]/10 dark:border-[#007AFF]/20 text-center">
                <span class="text-[11px] font-bold uppercase tracking-widest text-[#007AFF]">Total Likuiditas Bisnis</span>
                <div class="text-[32px] sm:text-[40px] font-black text-black dark:text-white tabular-nums tracking-tight mt-1">
                    {{ $dashboard['currency'] }} {{ number_format($dashboard['liquidity']['total'] ?? 0, 0, ',', '.') }}
                </div>
                @if(($dashboard['liquidity']['discrepancy_count'] ?? 0) > 0)
                    <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] text-[11px] font-bold">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                        {{ $dashboard['liquidity']['discrepancy_count'] }} selisih terdeteksi ({{ $dashboard['currency'] }} {{ number_format(abs($dashboard['liquidity']['total_variance'] ?? 0), 0, ',', '.') }})
                    </div>
                @else
                    <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] text-[11px] font-bold">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        Seluruh saldo seimbang
                    </div>
                @endif
            </div>

            {{-- 3-Column Breakdown --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- Column 1: Internal Cash (COOCA Ledger) --}}
                <div class="rounded-[16px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="landmark" class="w-4 h-4"></i>
                            </div>
                            <span class="text-xs font-bold text-black dark:text-white">Kas & Bank Internal</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">COOCA</span>
                    </div>

                    <div class="text-[20px] font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums">
                        {{ $dashboard['currency'] }} {{ number_format($dashboard['internal']['total'] ?? 0, 0, ',', '.') }}
                    </div>

                    <div class="space-y-1.5">
                        @foreach($dashboard['internal']['accounts'] ?? [] as $acc)
                            <div class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                        {{ $acc['type'] === 'cash' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                        {{ $acc['type'] === 'bank' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                        {{ $acc['type'] === 'ewallet' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : '' }}
                                    ">{{ $acc['type'] }}</span>
                                    <span class="font-medium text-black/80 dark:text-white/80">{{ $acc['name'] }}</span>
                                </div>
                                <span class="font-bold tabular-nums text-black dark:text-white">{{ number_format($acc['balance'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach

                        @if(empty($dashboard['internal']['accounts']))
                            <p class="text-[11px] text-black/40 dark:text-white/40 text-center py-3">Belum ada akun kas aktif</p>
                        @endif
                    </div>
                </div>

                {{-- Column 2: Cooca Pay Escrow --}}
                <div class="rounded-[16px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                <i data-lucide="shield" class="w-4 h-4"></i>
                            </div>
                            <span class="text-xs font-bold text-black dark:text-white">Escrow Cooca Pay</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-[#FF9500]/10 text-[#D97706]">Mengendap</span>
                    </div>

                    <div class="text-[20px] font-extrabold text-[#FF9500] tabular-nums">
                        {{ $dashboard['currency'] }} {{ number_format($dashboard['escrow']['net'] ?? 0, 0, ',', '.') }}
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                            <span class="text-black/60 dark:text-white/60">Bruto</span>
                            <span class="font-bold tabular-nums">{{ number_format($dashboard['escrow']['gross'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                            <span class="text-[#FF3B30]">Fee MDR</span>
                            <span class="font-bold tabular-nums text-[#FF3B30]">-{{ number_format($dashboard['escrow']['fee'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                            <span class="text-black/60 dark:text-white/60">Transaksi Pending</span>
                            <span class="font-bold">{{ $dashboard['escrow']['count'] ?? 0 }} trx</span>
                        </div>
                    </div>

                    @if(($dashboard['escrow']['count'] ?? 0) > 0)
                        <a href="{{ route('finance.settlements.index') }}"
                            class="block text-center text-[11px] font-bold text-[#007AFF] hover:underline mt-1">
                            Cairkan via Payout Hub →
                        </a>
                    @endif
                </div>

                {{-- Column 3: External Channels --}}
                <div class="rounded-[16px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                            </div>
                            <span class="text-xs font-bold text-black dark:text-white">Akun Eksternal</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-[#AF52DE]/10 text-[#AF52DE]">Manual</span>
                    </div>

                    <div class="text-[20px] font-extrabold text-[#AF52DE] tabular-nums">
                        {{ $dashboard['currency'] }} {{ number_format($dashboard['external']['total'] ?? 0, 0, ',', '.') }}
                    </div>

                    <div class="space-y-1.5">
                        @forelse($dashboard['external']['accounts'] ?? [] as $ext)
                            <div class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-black/80 dark:text-white/80">{{ $ext['channel_label'] }}</span>
                                    @if($ext['has_discrepancy'])
                                        <span class="w-2 h-2 rounded-full bg-[#FF3B30] animate-pulse"></span>
                                    @endif
                                </div>
                                <span class="font-bold tabular-nums {{ $ext['has_discrepancy'] ? 'text-[#FF3B30]' : 'text-black dark:text-white' }}">
                                    {{ number_format($ext['closing_balance'], 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-[11px] text-black/40 dark:text-white/40 text-center py-3">
                                Belum ada data. Isi lembar rekonsiliasi di bawah.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION 2: LEMBAR REKONSILIASI AKUN EKSTERNAL              --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-black/[0.01] dark:bg-white/[0.01]">
            <div>
                <h2 class="text-[16px] font-bold text-black dark:text-white">Lembar Rekonsiliasi Akun Eksternal</h2>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Input saldo awal & akhir setiap bulan untuk EDC, E-Wallet, atau Marketplace. Sistem akan menghitung selisih otomatis.</p>
            </div>
            <button type="button" @click="showFormModal = true"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shrink-0 shadow-sm">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah / Update Lembar</span>
            </button>
        </div>

        {{-- Existing Reconciliation Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-2.5 px-4">Channel / Akun</th>
                        @if($locations->count() > 1)
                            <th class="py-2.5 px-4">Cabang</th>
                        @endif
                        <th class="py-2.5 px-4 text-right">Saldo Awal</th>
                        <th class="py-2.5 px-4 text-right">Penerimaan</th>
                        <th class="py-2.5 px-4 text-right">Pencairan</th>
                        <th class="py-2.5 px-4 text-right">Saldo Akhir (Manual)</th>
                        <th class="py-2.5 px-4 text-right">Ekspektasi Sistem</th>
                        <th class="py-2.5 px-4 text-right">Selisih</th>
                        <th class="py-2.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    @forelse($reconciliations as $recon)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-black dark:text-white">{{ $recon->channel_label }}</span>
                                <div class="text-[10px] text-black/40 dark:text-white/40 font-mono">{{ $recon->channel_type }}</div>
                            </td>
                            @if($locations->count() > 1)
                                <td class="py-3.5 px-4 text-black/60 dark:text-white/60">{{ $recon->location?->name ?? 'Pusat' }}</td>
                            @endif
                            <td class="py-3.5 px-4 text-right tabular-nums font-medium">{{ number_format($recon->opening_balance, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-[#34C759]">+{{ number_format($recon->total_inflow, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-[#FF3B30]">-{{ number_format($recon->total_disbursement, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums font-bold text-black dark:text-white">{{ number_format($recon->closing_balance, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-black/50 dark:text-white/50">{{ number_format($recon->expected_closing, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums font-bold {{ $recon->hasDiscrepancy() ? 'text-[#FF3B30]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                                {{ $recon->variance >= 0 ? '+' : '' }}{{ number_format($recon->variance, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($recon->status === 'discrepancy')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">Selisih ⚠️</span>
                                @elseif($recon->status === 'submitted')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Seimbang ✓</span>
                                @elseif($recon->status === 'reviewed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/15 text-[#007AFF]">Reviewed</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-black/10 text-black/60">Draft</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $locations->count() > 1 ? 9 : 8 }}" class="py-12 text-center text-black/40 dark:text-white/40">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                        <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-[13px] font-medium text-black/70 dark:text-white/70">Belum ada data rekonsiliasi untuk periode ini</span>
                                    <span class="text-[11px]">Klik "Tambah / Update Lembar" untuk mulai mengisi saldo EDC, E-Wallet, atau Marketplace.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL: INPUT REKONSILIASI BARU (APPLE HIG SHEET)           --}}
    {{-- ========================================================== --}}
    <div x-show="showFormModal" x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-md"
        @keydown.escape.window="showFormModal = false">
        <div class="w-full sm:max-w-lg rounded-t-[22px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto"
            @click.outside="showFormModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100">

            <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between sticky top-0 bg-white dark:bg-[#1C1C1E] z-10">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Input Lembar Rekonsiliasi</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50">Update saldo akun eksternal untuk periode {{ \Carbon\Carbon::parse($period . '-01')->translatedFormat('F Y') }}</p>
                </div>
                <button @click="showFormModal = false" class="p-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 text-black/40 dark:text-white/40">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form @submit.prevent="submitRecon()" class="p-6 space-y-4">
                <input type="hidden" x-model="formData.period" value="{{ $period }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Tipe Channel *</label>
                        <select x-model="formData.channel_type" @change="autoLabel()" required
                            class="w-full h-11 sm:h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-[16px] sm:text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">-- Pilih Channel --</option>
                            @foreach($channelOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Label Akun *</label>
                        <input type="text" x-model="formData.channel_label" required placeholder="Contoh: EDC BCA - TID 12345"
                            class="w-full h-11 sm:h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                @if($locations->count() > 1)
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Cabang (Opsional)</label>
                        <select x-model="formData.location_id"
                            class="w-full h-11 sm:h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-[16px] sm:text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">Pusat / Default</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-3">
                    <h4 class="text-xs font-bold text-black dark:text-white">Data Saldo Bulanan</h4>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Saldo Awal Bulan *</label>
                            <input type="number" x-model.number="formData.opening_balance" required min="0" step="1" placeholder="0"
                                class="w-full h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Saldo Akhir Bulan *</label>
                            <input type="number" x-model.number="formData.closing_balance" required min="0" step="1" placeholder="0"
                                class="w-full h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Total Penerimaan Bulan Ini *</label>
                            <input type="number" x-model.number="formData.total_inflow" required min="0" step="1" placeholder="0"
                                class="w-full h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Total Pencairan / Settlement *</label>
                            <input type="number" x-model.number="formData.total_disbursement" required min="0" step="1" placeholder="0"
                                class="w-full h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>
                </div>

                {{-- Live Calculation Preview --}}
                <div class="rounded-[12px] p-3.5 text-xs space-y-1.5"
                    :class="Math.abs(calcVariance()) > 100 ? 'bg-[#FF3B30]/5 border border-[#FF3B30]/15' : 'bg-[#34C759]/5 border border-[#34C759]/15'">
                    <div class="flex justify-between">
                        <span class="text-black/60 dark:text-white/60">Ekspektasi Sistem (Awal + Masuk − Cair):</span>
                        <span class="font-bold tabular-nums" x-text="formatRupiah(calcExpected())"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-black/60 dark:text-white/60">Selisih:</span>
                        <span class="font-bold tabular-nums" :class="Math.abs(calcVariance()) > 100 ? 'text-[#FF3B30]' : 'text-[#34C759]'" x-text="formatRupiah(calcVariance())"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Catatan (Opsional)</label>
                    <input type="text" x-model="formData.notes" placeholder="Contoh: Pencairan GoPay tertunda, sudah klaim ke CS"
                        class="w-full h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                    <button type="button" @click="showFormModal = false"
                        class="h-10 px-4 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="h-10 px-6 rounded-[10px] bg-[#007AFF] hover:bg-[#0062CC] text-white text-xs font-bold transition active:scale-[0.98] flex items-center gap-2 shadow-sm disabled:opacity-40">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Rekonsiliasi'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function externalReconApp() {
    return {
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        showFormModal: false,
        isSubmitting: false,
        currentLocationId: '{{ $locationId ?? '' }}',
        formData: {
            channel_type: '',
            channel_label: '',
            location_id: '',
            period: '{{ $period }}',
            opening_balance: 0,
            total_inflow: 0,
            total_disbursement: 0,
            closing_balance: 0,
            notes: '',
        },

        init() {},

        autoLabel() {
            const labels = @json($channelOptions);
            if (this.formData.channel_type && labels[this.formData.channel_type]) {
                this.formData.channel_label = labels[this.formData.channel_type];
            }
        },

        calcExpected() {
            return (this.formData.opening_balance || 0) + (this.formData.total_inflow || 0) - (this.formData.total_disbursement || 0);
        },

        calcVariance() {
            return (this.formData.closing_balance || 0) - this.calcExpected();
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        },

        async submitRecon() {
            if (!this.formData.channel_type || !this.formData.channel_label) {
                alert('Pilih tipe channel dan label akun.');
                return;
            }

            this.isSubmitting = true;
            try {
                const res = await fetch("{{ route('finance.external-recon.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.formData),
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menyimpan rekonsiliasi.');
                }
            } catch (e) {
                alert('Terjadi kesalahan: ' + e.message);
            } finally {
                this.isSubmitting = false;
            }
        },
    };
}
</script>
@endsection
