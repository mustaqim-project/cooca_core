@extends('layouts.app', ['title' => 'Rekonsiliasi Bank'])

@section('content')
    <div x-data="{ uploadModalOpen: false }" class="max-w-[1360px] mx-auto space-y-5 pb-16">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                    <span class="text-black/70 dark:text-white/70">Keuangan &amp; Akuntansi</span>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span class="text-black dark:text-white font-medium">Rekonsiliasi Bank</span>
                </nav>
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Rekonsiliasi Rekening Bank</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">Cocokkan mutasi rekening koran bank dengan catatan kasir &amp; penerimaan kas otomatis</p>
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <a href="{{ route('finance.cash-bank.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5">
                    <i data-lucide="landmark" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Kas &amp; Bank</span>
                </a>
                <button type="button" @click="uploadModalOpen = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.35)]">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    <span>Unggah Rekening Koran</span>
                </button>
            </div>
        </header>

        {{-- ========================================================== --}}
        {{-- SUMMARY & PROGRESS CARD (BENTO)                            --}}
        {{-- ========================================================== --}}
        @if($selectedStatement)
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3.5">
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Rekening Bank</p>
                    <p class="text-[16px] font-bold text-black dark:text-white mt-1 truncate">{{ $selectedStatement->cashAccount->name }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $selectedStatement->filename }}</p>
                </div>

                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Total Baris Mutasi</p>
                    <p class="text-[20px] font-bold tabular-nums text-black dark:text-white mt-1">{{ $selectedStatement->total_lines }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Transaksi rekening koran</p>
                </div>

                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Terekonsiliasi</p>
                    <p class="text-[20px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">{{ $selectedStatement->reconciled_lines }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Dari {{ $selectedStatement->total_lines }} baris</p>
                </div>

                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Pencapaian</p>
                    <div class="mt-1 flex items-center justify-between">
                        <span class="text-[20px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                            {{ $selectedStatement->getProgressPercentage() }}%
                        </span>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $selectedStatement->status === 'completed' ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/12 text-[#C97800] dark:text-[#FF9F0A]' }}">
                            {{ $selectedStatement->status === 'completed' ? 'Selesai' : 'Sedang Berjalan' }}
                        </span>
                    </div>
                    <div class="w-full bg-black/5 dark:bg-white/10 rounded-full h-1.5 mt-2 overflow-hidden">
                        <div class="bg-[#007AFF] h-1.5 rounded-full transition-all duration-300" style="width: {{ $selectedStatement->getProgressPercentage() }}%"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================== --}}
        {{-- MAIN INTERFACE: STATEMENTS LIST & LINES MATCHER            --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

            {{-- Sidebar Statement History --}}
            <div class="lg:col-span-1 space-y-3">
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 space-y-3 shadow-xs">
                    <h3 class="text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Riwayat Berkas Impor</h3>

                    <div class="space-y-1.5">
                        @forelse($statements as $st)
                            <a href="{{ route('finance.reconciliations.index', ['statement_id' => $st->id]) }}"
                                class="block p-3 rounded-[12px] text-[12px] transition-all {{ $selectedStatement && $selectedStatement->id === $st->id ? 'bg-[#007AFF]/10 border border-[#007AFF]/25 text-[#007AFF]' : 'hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black/70 dark:text-white/70 border border-transparent' }}">
                                <div class="font-semibold text-[13px] text-black dark:text-white truncate">{{ $st->filename }}</div>
                                <div class="flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 mt-1">
                                    <span>{{ $st->statement_date->translatedFormat('d M Y') }}</span>
                                    <span class="tabular-nums font-medium">{{ $st->reconciled_lines }}/{{ $st->total_lines }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="py-6 text-center text-[12px] text-black/40 dark:text-white/40">
                                Belum ada berkas mutasi yang diunggah.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Main Statement Lines Table --}}
            <div class="lg:col-span-3 space-y-3">
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-xs">
                    <div class="p-4 sm:px-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span>
                            <h2 class="text-[14px] font-bold text-black dark:text-white">Lembar Pencocokan Mutasi (Auto-Matcher)</h2>
                        </div>
                        <span class="text-[12px] text-black/40 dark:text-white/40">Toleransi tanggal ±3 hari</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Tanggal</th>
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Keterangan Bank</th>
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Nominal (Rp)</th>
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Status &amp; Catatan Sistem</th>
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                @forelse($lines as $line)
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-4 sm:px-5 py-3.5 tabular-nums text-[13px] sm:text-[13.5px] font-medium text-black/75 dark:text-white/75">
                                            {{ $line->transaction_date->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="px-4 sm:px-5 py-3.5 text-[13.5px] sm:text-[14px] font-medium text-black dark:text-white">
                                            {{ $line->description }}
                                            @if($line->reference_number)
                                                <span class="block text-[12px] font-mono text-black/45 dark:text-white/45 mt-0.5">Ref: {{ $line->reference_number }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 sm:px-5 py-3.5 text-right tabular-nums">
                                            <span class="text-[14px] sm:text-[15px] font-bold font-mono tracking-tight {{ $line->type === 'credit' ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black dark:text-white' }}">
                                                {{ $line->type === 'credit' ? '+' : '-' }}{{ number_format($line->amount, 0, ',', '.') }}
                                            </span>
                                            <span class="block text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ $line->type }}</span>
                                        </td>
                                        <td class="px-4 sm:px-5 py-3.5">
                                            @if($line->status === 'reconciled')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    Terekonsiliasi
                                                </span>
                                            @elseif($line->status === 'matched')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">
                                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                                    Cocok Otomatis
                                                </span>
                                                <span class="block text-[12px] text-black/55 dark:text-white/55 mt-1">{{ $line->notes }}</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55">
                                                    Belum Cocok
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 sm:px-5 py-3.5 text-right">
                                            @if($line->status === 'reconciled')
                                                <form method="POST" action="{{ route('finance.reconciliations.unmatch', $line) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="h-9 px-3.5 rounded-[10px] text-[12.5px] font-medium text-black/60 dark:text-white/60 bg-black/[0.04] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors">
                                                        Batalkan
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('finance.reconciliations.match', $line) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#34C759] hover:bg-[#2FB34F] active:scale-[0.98] transition-all shadow-[0_2px_6px_rgba(52,199,89,0.3)] inline-flex items-center gap-1.5">
                                                        <i data-lucide="check" class="w-4 h-4"></i>
                                                        <span>Cocokkan</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center text-black/40 dark:text-white/40">
                                            Pilih berkas rekening koran di sisi kiri atau unggah berkas baru.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================================================== --}}
        {{-- MODAL UPLOAD BANK STATEMENT (APPLE HIG MODAL SHEET)        --}}
        {{-- ========================================================== --}}
        <div x-show="uploadModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @keydown.escape.window="uploadModalOpen = false">
            <div class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[22px] shadow-2xl border border-black/10 dark:border-white/10 p-6 space-y-4"
                @click.outside="uploadModalOpen = false">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <h2 class="text-[17px] font-bold text-black dark:text-white">Unggah Rekening Koran Bank</h2>
                    <button type="button" @click="uploadModalOpen = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('finance.reconciliations.upload') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Rekening Kas / Bank Tujuan *</label>
                        <select name="cash_account_id" required class="w-full h-11 sm:h-10 px-3 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                            @foreach($cashAccounts as $ca)
                                <option value="{{ $ca->id }}">{{ $ca->name }} ({{ $ca->account_number ?? 'Internal' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tanggal Rekening Koran *</label>
                        <input type="date" name="statement_date" value="{{ now()->toDateString() }}" required
                            class="w-full h-11 sm:h-10 px-3 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Berkas CSV Rekening Koran</label>
                        <input type="file" name="statement_file" accept=".csv,.txt"
                            class="w-full text-[13px] text-black/70 dark:text-white/70 file:mr-4 file:py-2.5 file:px-4 file:rounded-[10px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/15">
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Format kolom: Tanggal, Keterangan, Nominal, Tipe (debit/credit)</p>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Atau Tempel Baris Mutasi (CSV Baris Demi Baris)</label>
                        <textarea name="manual_entries" rows="4" placeholder="2026-09-20, Setoran QRIS Gerai, 150000, credit&#10;2026-09-20, Biaya Admin Bank, 5000, debit"
                            class="w-full p-3 text-[16px] sm:text-[12px] font-mono bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]"></textarea>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-black/5 dark:border-white/10">
                        <button type="button" @click="uploadModalOpen = false" class="min-h-[44px] sm:min-h-0 h-11 sm:h-10 px-4 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">Batal</button>
                        <button type="submit" class="min-h-[44px] sm:min-h-0 h-11 sm:h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] shadow-md">Proses &amp; Cocokkan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
