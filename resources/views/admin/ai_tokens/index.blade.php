@extends('layouts.admin', [
    'title' => 'Monitoring Token AI — Admin Console',
    'headerTitle' => 'Monitoring Token AI Google Gemini',
    'headerSubtitle' => 'Pantau konsumsi token AI platform, analisis intent, dan bisnis pengguna AI tertinggi'
])

@section('content')
<div class="space-y-6" x-data="{ grantModalOpen: false, selectedBizId: '', tokenAmount: 10000000, reasonText: '' }">

    <!-- Top Action Bar -->
    <div class="flex justify-end">
        <button type="button" @click="grantModalOpen = true" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(175,82,222,0.3)]">
            <i data-lucide="plus-circle" class="w-4 h-4" stroke-width="1.5"></i>
            <span>Top-Up / Grant Token AI Manual</span>
        </button>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Token Bulan Ini (MTD)</span>
            <div class="mt-2 flex items-end justify-between gap-2">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalTokensMonth, 0, ',', '.') }}</span>
                <i data-lucide="sparkles" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2]" stroke-width="1.5"></i>
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Konsumsi bulan berjalan</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Token All-Time</span>
            <div class="mt-2 flex items-end justify-between gap-2">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalTokensAllTime, 0, ',', '.') }}</span>
                <i data-lucide="database" class="w-4 h-4 text-[#5856D6] dark:text-[#5E5CE6]" stroke-width="1.5"></i>
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Akumulasi sejak awal peluncuran</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Kueri Eksekusi</span>
            <div class="mt-2 flex items-end justify-between gap-2">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalQueriesCount, 0, ',', '.') }}</span>
                <i data-lucide="cpu" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i>
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Panggilan API AI berhasil</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Bisnis Aktif Pakai AI</span>
            <div class="mt-2 flex items-end justify-between gap-2">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">{{ number_format($activeAiBusinessesCount, 0, ',', '.') }}</span>
                <i data-lucide="building-2" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Workspace memanfaatkan AI bulan ini</div>
        </div>
    </div>

    <!-- 2 Columns: Intent Breakdown & Top Businesses -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 lg:col-span-6">
            <div class="border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2]" stroke-width="1.5"></i>
                    <span>Penggunaan Token Berdasarkan Fitur / Intent</span>
                </h3>
            </div>
            @if($intentBreakdown->isEmpty())
            <div class="text-[13px] text-black/45 dark:text-white/45 py-8 text-center">Belum ada data eksekusi AI token tercatat.</div>
            @else
            <div class="space-y-3 mt-4">
                @foreach($intentBreakdown as $intent)
                @php
                    $pct = $totalTokensAllTime > 0 ? round(($intent->total_tokens / $totalTokensAllTime) * 100, 1) : 0;
                @endphp
                <div class="p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] text-[13px]">
                    <div class="flex items-center justify-between font-medium">
                        <span class="text-black dark:text-white capitalize">{{ str_replace('_', ' ', $intent->intent ?? 'general') }}</span>
                        <span class="text-[#AF52DE] dark:text-[#BF5AF2] font-semibold tabular-nums">{{ number_format((float) $intent->total_tokens, 0, ',', '.') }} Token ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full h-1.5 mt-2 bg-black/[0.08] dark:bg-white/[0.1] rounded-full overflow-hidden">
                        <div class="h-full bg-[#AF52DE] rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="text-[11px] text-black/45 dark:text-white/45 text-right tabular-nums mt-1">{{ number_format($intent->queries_count) }} kali eksekusi</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 lg:col-span-6">
            <div class="border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="flame" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]" stroke-width="1.5"></i>
                    <span>Top 10 Bisnis Konsumen AI Terbesar</span>
                </h3>
            </div>
            @if($topBusinesses->isEmpty())
            <div class="text-[13px] text-black/45 dark:text-white/45 py-8 text-center">Belum ada data tenant yang menggunakan AI.</div>
            @else
            <div class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @foreach($topBusinesses as $index => $top)
                <div class="py-3 flex items-center justify-between text-[13px] hover:bg-black/[0.02] dark:hover:bg-white/[0.03] px-2 rounded-[10px] transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] font-semibold text-[11px] tabular-nums flex items-center justify-center">{{ $index + 1 }}</span>
                        <div>
                            <div class="font-medium text-black dark:text-white"><a href="{{ route('admin.businesses.show', $top['business_id']) }}" class="hover:text-[#AF52DE] dark:hover:text-[#BF5AF2] transition-colors">{{ $top['business_name'] }}</a></div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">{{ $top['queries_count'] }} kali kueri</div>
                        </div>
                    </div>
                    <div class="text-right tabular-nums">
                        <div class="font-bold text-[#AF52DE] dark:text-[#BF5AF2]">{{ number_format($top['total_tokens'], 0, ',', '.') }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">token</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Activity Logs Table -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-4 sm:px-5 py-3.5 border-b border-black/5 dark:border-white/10">
            <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i>
                <span>Log Aktivitas Token AI Terbaru (20 Transaksi Terakhir)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Waktu</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Bisnis / Pengguna</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Intent / Fitur</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Model Engine</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Input / Output</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Total Token</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($recentUsages as $usage)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ $usage->created_at?->format('d M H:i:s') ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $usage->business_name }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">{{ $usage->user?->name ?? 'System' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]"><span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span>{{ $usage->intent ?? 'general' }}</span>
                        </td>
                        <td class="px-4 py-3 text-[12px] text-black/50 dark:text-white/50">{{ $usage->model_name ?? 'gemini-2.5-flash' }}</td>
                        <td class="px-4 py-3 text-right text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ number_format($usage->input_tokens) }} / {{ number_format($usage->output_tokens) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-[#AF52DE] dark:text-[#BF5AF2] tabular-nums">{{ number_format($usage->total_tokens) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center"><p class="text-[15px] font-semibold text-black dark:text-white">Belum ada aktivitas AI</p><p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Belum ada riwayat aktivitas kueri AI.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Grant Token Modal (Sheet) -->
    <div x-show="grantModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div @click.away="grantModalOpen = false" class="sheet-material rounded-[20px] w-full max-w-md p-6 space-y-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                    <i data-lucide="sparkles" class="w-5 h-5 text-[#AF52DE] dark:text-[#BF5AF2]" stroke-width="1.5"></i>
                    <span>Top-Up / Grant Token AI</span>
                </div>
                <button type="button" @click="grantModalOpen = false" class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10"><i data-lucide="x" class="w-4 h-4" stroke-width="1.5"></i></button>
            </div>
            <form :action="'{{ url('admin/ai-tokens') }}/' + selectedBizId + '/grant'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Pilih Bisnis / Tenant</label>
                    <select x-model="selectedBizId" required class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="">-- Pilih Bisnis --</option>
                        @foreach($allBusinesses as $biz)
                        <option value="{{ $biz->id }}">{{ $biz->name }} ({{ $biz->slug }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Jumlah Token</label>
                    <input type="number" name="tokens" x-model="tokenAmount" min="100000" step="100000" required class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Default allowance Core: 10.000.000 token</p>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Alasan / Catatan (Opsional)</label>
                    <input type="text" name="reason" x-model="reasonText" placeholder="Contoh: Pembelian Add-on Top-up Manual" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex gap-3">
                    <button type="button" @click="grantModalOpen = false" class="flex-1 h-10 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition">Batal</button>
                    <button type="submit" :disabled="!selectedBizId" class="flex-1 h-10 rounded-[10px] text-[13px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] disabled:opacity-50 transition-all">Kirim Token</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection