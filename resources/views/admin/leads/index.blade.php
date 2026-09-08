@extends('layouts.admin', [
    'title' => 'CMS Unduhan & Leads — Admin Console',
    'headerTitle' => 'Database Leads Pengunduh Template',
    'headerSubtitle' => 'Daftar calon pengguna UMKM yang telah mengunduh spreadsheet template gratis untuk follow-up sales & marketing'
])

@section('content')
<div class="space-y-6">

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="glass-card p-4 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Pengunduh (Leads)</span>
                <span class="text-2xl font-black text-white font-mono">{{ number_format($totalLeads) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold text-emerald-400 block">Leads Masuk Hari Ini</span>
                <span class="text-2xl font-black text-emerald-400 font-mono">{{ number_format($todayLeads) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <i data-lucide="calendar" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Top Action Bar -->
    <div class="glass-card p-4 rounded-2xl flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.leads.index') }}" class="flex items-center gap-3 flex-1 max-w-lg">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, WhatsApp, nama usaha..." class="w-full pl-10 pr-4 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
            </div>
            <select name="template" onchange="this.form.submit()" class="px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none">
                <option value="">Semua Template</option>
                <option value="pembukuan-warung-excel" {{ request('template') === 'pembukuan-warung-excel' ? 'selected' : '' }}>Pembukuan Warung</option>
                <option value="laporan-keuangan-sederhana" {{ request('template') === 'laporan-keuangan-sederhana' ? 'selected' : '' }}>Laporan Keuangan</option>
                <option value="stok-opname-excel" {{ request('template') === 'stok-opname-excel' ? 'selected' : '' }}>Stok Opname</option>
                <option value="invoice-sederhana" {{ request('template') === 'invoice-sederhana' ? 'selected' : '' }}>Invoice Sederhana</option>
            </select>
        </form>

        <a href="{{ route('admin.leads.export') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 transition-all shrink-0">
            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
            <span>Ekspor Data (CSV)</span>
        </a>
    </div>

    <!-- Leads Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">Nama Prospek</th>
                        <th class="py-3.5 px-4 font-semibold">WhatsApp / No HP</th>
                        <th class="py-3.5 px-4 font-semibold">Nama Usaha / Email</th>
                        <th class="py-3.5 px-4 font-semibold">Template yang Diunduh</th>
                        <th class="py-3.5 px-4 font-semibold">Waktu Unduh</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Follow Up</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-white">
                            {{ $lead->name }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-300">
                            {{ $lead->phone }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-white font-semibold block">{{ $lead->business_name ?? '-' }}</span>
                            <span class="text-[10px] text-slate-500">{{ $lead->email ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                {{ $lead->template_name }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                            {{ $lead->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ $lead->whatsapp_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600/20 hover:bg-emerald-600/40 border border-emerald-500/30 text-emerald-300 text-[11px] font-bold transition-all">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Chat WA</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 text-xs">
                            Belum ada prospek yang mengunduh template.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
        <div class="p-4 border-t border-slate-800/80">
            {{ $leads->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
