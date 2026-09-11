@extends('layouts.admin', [
    'title' => 'CMS Unduhan & Leads — Admin Console',
    'headerTitle' => 'Database Leads Pengunduh Template',
    'headerSubtitle' => 'Daftar calon pengguna UMKM yang telah mengunduh spreadsheet template gratis untuk follow-up sales & marketing'
])

@section('content')
<div class="space-y-6">

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
            <div>
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Total Pengunduh (Leads)</span>
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ number_format($totalLeads) }}</span>
            </div>
            <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center"><i data-lucide="users" class="w-5 h-5" stroke-width="1.5"></i></div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
            <div>
                <span class="text-[12px] font-medium text-[#34C759] dark:text-[#30D158] block">Leads Masuk Hari Ini</span>
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ number_format($todayLeads) }}</span>
            </div>
            <div class="w-10 h-10 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center"><i data-lucide="calendar" class="w-5 h-5" stroke-width="1.5"></i></div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.leads.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1 max-w-lg">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, WhatsApp, nama usaha..." class="w-full h-9 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
            <select name="template" onchange="this.form.submit()" class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                <option value="">Semua Template</option>
                <option value="pembukuan-warung-excel" {{ request('template') === 'pembukuan-warung-excel' ? 'selected' : '' }}>Pembukuan Warung</option>
                <option value="laporan-keuangan-sederhana" {{ request('template') === 'laporan-keuangan-sederhana' ? 'selected' : '' }}>Laporan Keuangan</option>
                <option value="stok-opname-excel" {{ request('template') === 'stok-opname-excel' ? 'selected' : '' }}>Stok Opname</option>
                <option value="invoice-sederhana" {{ request('template') === 'invoice-sederhana' ? 'selected' : '' }}>Invoice Sederhana</option>
            </select>
        </form>
        <a href="{{ route('admin.leads.export') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shrink-0">
            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i><span>Ekspor Data (CSV)</span>
        </a>
    </div>

    <!-- Leads Table -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Nama Prospek</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">WhatsApp / No HP</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Nama Usaha / Email</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Template yang Diunduh</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Waktu Unduh</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Follow Up</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 font-medium text-black dark:text-white">{{ $lead->name }}</td>
                        <td class="px-4 py-3 tabular-nums text-black/60 dark:text-white/60">{{ $lead->phone }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-black dark:text-white block">{{ $lead->business_name ?? '-' }}</span>
                            <span class="text-[11px] text-black/45 dark:text-white/45">{{ $lead->email ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]"><span class="w-1.5 h-1.5 rounded-full bg-[#5856D6]"></span> {{ $lead->template_name }}</span>
                        </td>
                        <td class="px-4 py-3 text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ $lead->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ $lead->whatsapp_url }}" target="_blank" rel="noopener" class="h-7 px-2.5 rounded-[8px] text-[12px] font-medium text-[#34C759] dark:text-[#30D158] bg-[#34C759]/10 hover:bg-[#34C759]/15 active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5" stroke-width="1.5"></i><span>Chat WA</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center">
                        <i data-lucide="users" class="w-12 h-12 mx-auto text-black/20 dark:text-white/20" stroke-width="1.5"></i>
                        <p class="text-[15px] font-semibold text-black dark:text-white mt-3">Belum ada leads</p>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Belum ada prospek yang mengunduh template.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leads->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">{{ $leads->links() }}</div>
        @endif
    </div>
</div>
@endsection