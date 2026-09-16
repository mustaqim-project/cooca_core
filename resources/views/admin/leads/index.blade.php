@extends('layouts.admin', [
    'title' => 'Database Leads & Pengunduh - Admin Console',
    'headerTitle' => 'Database Leads Pengunduh Template',
    'headerSubtitle' => 'Daftar calon pengguna UMKM yang telah mengunduh spreadsheet template gratis untuk follow-up sales & marketing Cooca',
])

@section('content')
    <div class="space-y-6">

        <!-- Bento KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
            <!-- Card 1: Total Leads -->
            <div class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Total Prospek (Leads)</span>
                    <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-4 h-4" stroke-width="2"></i>
                    </div>
                </div>
                <div class="text-[28px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ number_format($totalLeads) }}
                </div>
                <div class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mt-1.5">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="2"></i>
                    <span>Total pengunduh spreadsheet UMKM</span>
                </div>
            </div>

            <!-- Card 2: Leads Hari Ini -->
            <div class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-semibold uppercase tracking-wider text-[#34C759] dark:text-[#30D158]">Leads Masuk Hari Ini</span>
                    <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                        <i data-lucide="calendar" class="w-4 h-4" stroke-width="2"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <div class="text-[28px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                        {{ number_format($todayLeads) }}
                    </div>
                    @if($todayLeads > 0)
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#34C759] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#34C759]"></span>
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mt-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-[#34C759] dark:text-[#30D158]" stroke-width="2"></i>
                    <span>Tercatat sejak pukul 00:00 WIB</span>
                </div>
            </div>

            <!-- Card 3: Template Terpopuler / Rasio -->
            <div class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm hover:shadow-md transition-all sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-semibold uppercase tracking-wider text-[#5856D6] dark:text-[#5E5CE6]">Template Terpopuler</span>
                    <div class="w-9 h-9 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                        <i data-lucide="sparkles" class="w-4 h-4" stroke-width="2"></i>
                    </div>
                </div>
                @php
                    $topTemplate = $templatesCount->sortByDesc('count')->first();
                @endphp
                <div class="text-[17px] font-bold text-black dark:text-white truncate">
                    {{ $topTemplate ? $topTemplate->template_name : 'Belum Ada Data' }}
                </div>
                <div class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mt-1.5">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-[#5856D6] dark:text-[#5E5CE6]" stroke-width="2"></i>
                    <span>
                        @if($topTemplate)
                            Diunduh <strong>{{ number_format($topTemplate->count) }}x</strong> oleh calon klien
                        @else
                            Menunggu prospek pertama
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Bento Search & Filter Toolbar -->
        <div class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-sm flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.leads.index') }}"
                class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 max-w-2xl">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <i data-lucide="search"
                        class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                        stroke-width="1.8"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama prospek, nomor WhatsApp, nama usaha, atau email..."
                        class="w-full h-10 pl-10 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                </div>

                <!-- Template Dropdown Filter -->
                <div class="relative shrink-0 sm:w-56">
                    <select name="template" onchange="this.form.submit()"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] font-medium text-black/75 dark:text-white/75 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                        <option value="">Semua Template Spreadsheet</option>
                        @if($templatesCount->isNotEmpty())
                            @foreach($templatesCount as $item)
                                <option value="{{ $item->template_slug }}" {{ request('template') === $item->template_slug ? 'selected' : '' }}>
                                    {{ $item->template_name }} ({{ $item->count }})
                                </option>
                            @endforeach
                        @else
                            <option value="pembukuan-warung-excel" {{ request('template') === 'pembukuan-warung-excel' ? 'selected' : '' }}>Pembukuan Warung</option>
                            <option value="laporan-keuangan-sederhana" {{ request('template') === 'laporan-keuangan-sederhana' ? 'selected' : '' }}>Laporan Keuangan</option>
                            <option value="stok-opname-excel" {{ request('template') === 'stok-opname-excel' ? 'selected' : '' }}>Stok Opname</option>
                            <option value="invoice-sederhana" {{ request('template') === 'invoice-sederhana' ? 'selected' : '' }}>Invoice Sederhana</option>
                        @endif
                    </select>
                </div>

                @if (request('search') || request('template'))
                    <a href="{{ route('admin.leads.index') }}"
                        class="h-10 px-3 rounded-[12px] text-[13px] font-medium text-[#FF3B30] dark:text-[#FF453A] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] transition-all inline-flex items-center justify-center gap-1.5 shrink-0">
                        <i data-lucide="x" class="w-3.5 h-3.5" stroke-width="2"></i>
                        <span>Reset Filter</span>
                    </a>
                @endif
            </form>

            <!-- Export CSV Button -->
            <a href="{{ route('admin.leads.export') }}"
                class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2 shrink-0 border border-black/[0.04] dark:border-white/[0.06]">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="2"></i>
                <span>Ekspor Spreadsheet (CSV)</span>
            </a>
        </div>

        <!-- Bento Leads Table Container -->
        <div class="rounded-[22px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                Prospek UMKM
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                WhatsApp / Kontak
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                Usaha & Email
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                Template Diunduh
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                Waktu Unduh
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider text-right">
                                Aksi Follow-Up
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($leads as $lead)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <!-- Prospek UMKM -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-[14px] flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($lead->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-black dark:text-white text-[14px]">
                                                {{ $lead->name }}
                                            </div>
                                            @if($lead->business_name)
                                                <div class="text-[11px] font-medium text-black/50 dark:text-white/50">
                                                    {{ $lead->business_name }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- WhatsApp / No HP -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5 font-mono text-[13px] font-medium text-black/80 dark:text-white/80 tabular-nums">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-black/40 dark:text-white/40" stroke-width="1.8"></i>
                                        <span>{{ $lead->phone }}</span>
                                    </div>
                                </td>

                                <!-- Usaha & Email -->
                                <td class="px-5 py-3.5">
                                    @if($lead->email)
                                        <div class="flex items-center gap-1.5 text-[12px] text-black/70 dark:text-white/70 truncate max-w-xs">
                                            <i data-lucide="mail" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0" stroke-width="1.8"></i>
                                            <span class="truncate">{{ $lead->email }}</span>
                                        </div>
                                    @else
                                        <span class="text-[12px] text-black/30 dark:text-white/30">-</span>
                                    @endif
                                </td>

                                <!-- Template Diunduh -->
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] border border-[#5856D6]/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#5856D6]"></span>
                                        {{ $lead->template_name }}
                                    </span>
                                </td>

                                <!-- Waktu Unduh -->
                                <td class="px-5 py-3.5 text-[12px] text-black/60 dark:text-white/60 tabular-nums">
                                    <div class="font-medium text-black/80 dark:text-white/80">
                                        {{ $lead->created_at->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ $lead->created_at->format('H:i') }} WIB ({{ $lead->created_at->diffForHumans() }})
                                    </div>
                                </td>

                                <!-- Follow Up Button -->
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ $lead->whatsapp_url }}" target="_blank" rel="noopener"
                                        title="Kirim pesan follow-up otomatis ke WhatsApp calon pengguna"
                                        class="h-9 px-3.5 rounded-[10px] text-[12px] font-semibold text-[#248A3D] dark:text-[#30D158] bg-[#34C759]/12 hover:bg-[#34C759]/20 border border-[#34C759]/25 active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5 shadow-sm">
                                        <i data-lucide="message-circle" class="w-4 h-4" stroke-width="2"></i>
                                        <span>Chat WhatsApp</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="w-14 h-14 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto mb-3">
                                        <i data-lucide="users" class="w-7 h-7" stroke-width="1.5"></i>
                                    </div>
                                    <p class="text-[16px] font-bold text-black dark:text-white">Belum Ada Data Prospek</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 max-w-md mx-auto">
                                        @if(request('search') || request('template'))
                                            Tidak ditemukan prospek dengan kata kunci atau filter yang Anda pilih. Coba sesuaikan filter pencarian.
                                        @else
                                            Belum ada pengunjung yang mengunduh spreadsheet template gratis dari halaman unduhan.
                                        @endif
                                    </p>
                                    @if(request('search') || request('template'))
                                        <div class="mt-4">
                                            <a href="{{ route('admin.leads.index') }}"
                                                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition inline-flex items-center gap-1.5">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5" stroke-width="2"></i>
                                                <span>Tampilkan Semua Prospek</span>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bento Pagination Footer -->
            @if ($leads->hasPages())
                <div class="px-5 py-4 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.01]">
                    {{ $leads->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
