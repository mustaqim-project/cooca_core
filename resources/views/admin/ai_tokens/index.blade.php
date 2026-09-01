@extends('layouts.admin', [
    'title' => 'Monitoring Token AI — Admin Console',
    'headerTitle' => 'Monitoring Token AI Google Gemini',
    'headerSubtitle' => 'Pantau konsumsi token AI platform, analisis intent, dan bisnis pengguna AI tertinggi'
])

@section('content')
<div class="space-y-8" x-data="{ grantModalOpen: false, selectedBizId: '', tokenAmount: 10000000, reasonText: '' }">

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-sm font-semibold flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Top Action Bar -->
    <div class="flex justify-end">
        <button type="button" @click="grantModalOpen = true" class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-purple-500/20 transition">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Top-Up / Grant Token AI Manual</span>
        </button>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="glass-card p-6 rounded-2xl border-purple-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-purple-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Token Bulan Ini (MTD)</span>
                <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                {{ number_format($totalTokensMonth, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-slate-400">
                Konsumsi bulan berjalan
            </div>
        </div>

        <div class="glass-card p-6 rounded-2xl border-indigo-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-indigo-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Token All-Time</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <i data-lucide="database" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                {{ number_format($totalTokensAllTime, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-slate-400">
                Akumulasi sejak awal peluncuran
            </div>
        </div>

        <div class="glass-card p-6 rounded-2xl border-cyan-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-cyan-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Kueri Eksekusi</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                    <i data-lucide="cpu" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                {{ number_format($totalQueriesCount, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-slate-400">
                Panggilan API AI berhasil
            </div>
        </div>

        <div class="glass-card p-6 rounded-2xl border-emerald-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bisnis Aktif Pakai AI</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                {{ number_format($activeAiBusinessesCount, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-slate-400">
                Workspace memanfaatkan AI bulan ini
            </div>
        </div>
    </div>

    <!-- 2 Columns Grid: Usage by Intent & Top Consuming Businesses -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Breakdown by Intent (6 cols) -->
        <div class="glass-card rounded-3xl border border-slate-800 p-6 lg:col-span-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-purple-400"></i>
                    <span>Penggunaan Token Berdasarkan Fitur / Intent</span>
                </h3>
            </div>

            @if($intentBreakdown->isEmpty())
            <div class="text-xs text-slate-500 py-8 text-center">
                Belum ada data eksekusi AI token tercatat.
            </div>
            @else
            <div class="space-y-3">
                @foreach($intentBreakdown as $intent)
                @php
                    $pct = $totalTokensAllTime > 0 ? round(($intent->total_tokens / $totalTokensAllTime) * 100, 1) : 0;
                @endphp
                <div class="p-3 rounded-2xl bg-slate-900/60 border border-slate-800 text-xs space-y-1.5">
                    <div class="flex items-center justify-between font-medium">
                        <span class="font-mono text-white font-bold capitalize">{{ str_replace('_', ' ', $intent->intent ?? 'general') }}</span>
                        <span class="text-purple-400 font-mono font-bold">{{ number_format((float) $intent->total_tokens, 0, ',', '.') }} Token ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="text-[10px] text-slate-500 text-right font-mono">
                        {{ number_format($intent->queries_count) }} kali eksekusi
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Right: Top 10 Businesses (6 cols) -->
        <div class="glass-card rounded-3xl border border-slate-800 p-6 lg:col-span-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="flame" class="w-4 h-4 text-amber-400"></i>
                    <span>Top 10 Bisnis Konsumen AI Terbesar</span>
                </h3>
            </div>

            @if($topBusinesses->isEmpty())
            <div class="text-xs text-slate-500 py-8 text-center">
                Belum ada data tenant yang menggunakan AI.
            </div>
            @else
            <div class="divide-y divide-slate-800/60">
                @foreach($topBusinesses as $index => $top)
                <div class="py-3 flex items-center justify-between text-xs hover:bg-slate-900/40 px-2 rounded-xl transition">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 text-slate-400 font-mono font-bold text-[10px] flex items-center justify-center">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <div class="font-bold text-white">
                                <a href="{{ route('admin.businesses.show', $top['business_id']) }}" class="hover:text-purple-400 transition">
                                    {{ $top['business_name'] }}
                                </a>
                            </div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $top['queries_count'] }} kali kueri</div>
                        </div>
                    </div>
                    <div class="text-right font-mono">
                        <div class="font-bold text-purple-300">{{ number_format($top['total_tokens'], 0, ',', '.') }}</div>
                        <div class="text-[9px] text-slate-500">token</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Recent AI Token Activity Logs Table -->
    <div class="glass-card rounded-3xl overflow-hidden border border-slate-800">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-cyan-400"></i>
                <span>Log Aktivitas Token AI Terbaru (20 Transaksi Terakhir)</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/60 font-semibold">
                        <th class="py-3.5 px-4">Waktu</th>
                        <th class="py-3.5 px-4">Bisnis / Pengguna</th>
                        <th class="py-3.5 px-4">Intent / Fitur</th>
                        <th class="py-3.5 px-4">Model Engine</th>
                        <th class="py-3.5 px-4 text-right">Input / Output</th>
                        <th class="py-3.5 px-4 text-right">Total Token</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($recentUsages as $usage)
                    <tr class="hover:bg-slate-900/40 transition">
                        <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                            {{ $usage->created_at?->format('d M H:i:s') ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 font-sans">
                            <div class="font-bold text-white">{{ $usage->business_name }}</div>
                            <div class="text-[10px] text-slate-400">{{ $usage->user?->name ?? 'System' }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-sans">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-500/10 text-purple-300 border border-purple-500/20">
                                {{ $usage->intent ?? 'general' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                            {{ $usage->model_name ?? 'gemini-2.5-flash' }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-400 text-[11px]">
                            {{ number_format($usage->input_tokens) }} / {{ number_format($usage->output_tokens) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-purple-400">
                            {{ number_format($usage->total_tokens) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 font-sans">
                            Belum ada riwayat aktivitas kueri AI.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Grant Token Modal Dialog -->
    <div x-show="grantModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div @click.away="grantModalOpen = false" class="w-full max-w-md glass-card bg-slate-900 border border-slate-700 rounded-3xl p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2 text-white font-bold text-base">
                    <i data-lucide="sparkles" class="w-5 h-5 text-purple-400"></i>
                    <span>Top-Up / Grant Token AI</span>
                </div>
                <button type="button" @click="grantModalOpen = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/ai-tokens') }}/' + selectedBizId + '/grant'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Pilih Bisnis / Tenant</label>
                    <select x-model="selectedBizId" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white focus:border-purple-500 outline-none">
                        <option value="">-- Pilih Bisnis --</option>
                        @foreach($allBusinesses as $biz)
                        <option value="{{ $biz->id }}">{{ $biz->name }} ({{ $biz->slug }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Jumlah Token</label>
                    <input type="number" name="tokens" x-model="tokenAmount" min="100000" step="100000" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white font-mono focus:border-purple-500 outline-none">
                    <div class="text-[10px] text-slate-500 mt-1">Default allowance Core: 10.000.000 token</div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Alasan / Catatan (Opsional)</label>
                    <input type="text" name="reason" x-model="reasonText" placeholder="Contoh: Pembelian Add-on Top-up Manual" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white focus:border-purple-500 outline-none">
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="grantModalOpen = false" class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-xs hover:bg-slate-800 transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="!selectedBizId" class="flex-1 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white font-bold text-xs transition">
                        Kirim Token
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
