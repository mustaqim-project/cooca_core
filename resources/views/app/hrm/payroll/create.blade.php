@extends('layouts.app', [
    'title' => 'Buat Penggajian Bulanan',
    'headerTitle' => 'Buat Penggajian Bulanan',
    'headerSubtitle' => 'Kalkulasi batch gaji karyawan, potongan BPJS, PPh 21 TER, dan kasbon untuk periode berjalan.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-24" x-data="{
    periodMonth: {{ $month }},
    periodYear: {{ $year }},
    includeThr: false,
    formatRupiah(val) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
    }
}">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('hrm.index', ['tab' => 'payrolls']) }}"
            class="inline-flex items-center gap-2 text-[13px] font-semibold text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white transition group">
            <div class="w-8 h-8 rounded-[10px] bg-black/5 dark:bg-white/10 flex items-center justify-center group-hover:bg-black/10 dark:group-hover:bg-white/15 transition">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/70 dark:text-white/70"></i>
            </div>
            <span>Kembali ke Hub Penggajian</span>
        </a>

        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] text-[12px] font-bold">
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            <span>{{ $memberships->count() }} Karyawan Terdaftar</span>
        </div>
    </div>

    <form method="POST" action="{{ route('hrm.payrolls.store') }}" class="space-y-6">
        @csrf

        <!-- Bento Card: Pengaturan Periode & Opsi THR -->
        <div class="p-5 sm:p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-5">
            <div class="border-b border-black/5 dark:border-white/5 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[16px] font-bold text-black dark:text-white">Konfigurasi Periode Penggajian</h2>
                        <p class="text-[12px] text-black/60 dark:text-white/60">Tentukan bulan kerja dan sertakan opsi pembayaran THR jika bertepatan hari raya keagamaan.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4.5 items-end">
                <!-- Pilihan Bulan -->
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">
                        Bulan Penggajian <span class="text-[#FF3B30]">*</span>
                    </label>
                    <div class="relative">
                        <select name="period_month" x-model="periodMonth" required
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 focus:outline-none transition cursor-pointer">
                            @for($m = 1; $m <= 12; $m++)
                                @php
                                    $mNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                                @endphp
                                <option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>{{ $mNames[$m] }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Pilihan Tahun -->
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">
                        Tahun <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="number" name="period_year" x-model="periodYear" required min="2020" max="2050" value="{{ $year }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 focus:outline-none transition">
                </div>

                <!-- Opsi THR Prorata Join Date -->
                <div>
                    <label class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 flex items-center gap-3 cursor-pointer hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition min-h-[44px]">
                        <input type="checkbox" name="include_thr" value="1" x-model="includeThr"
                            class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-0 cursor-pointer">
                        <div class="min-w-0">
                            <span class="text-[13px] font-bold text-black dark:text-white block">Sertakan THR Keagamaan</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60 block leading-tight">Prorata otomatis sesuai tanggal masuk (Permenaker 6/2016)</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Tabel Masukan Variabel Gaji per Karyawan -->
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden space-y-2">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Rincian Komponen Upah &amp; Variabel Karyawan</h3>
                        <p class="text-[12px] text-black/60 dark:text-white/60">Sesuaikan lembur, hari kerja pekerja harian, dan verifikasi potongan kasbon sebelum kalkulasi final.</p>
                    </div>
                </div>

                <div class="text-[12px] text-black/60 dark:text-white/60 font-medium">
                    Karyawan dicentang akan diproses dalam batch ini
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[12.5px] border-collapse min-w-[960px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">Karyawan</th>
                            <th class="py-3.5 px-3">Tipe &amp; Pokok (Rp)</th>
                            <th class="py-3.5 px-3">Tunjangan (Rp)</th>
                            <th class="py-3.5 px-3">Hari Kerja</th>
                            <th class="py-3.5 px-3">Lembur (Rp)</th>
                            <th class="py-3.5 px-3">Komisi (Rp)</th>
                            <th class="py-3.5 px-3">Potongan Kasbon (Rp)</th>
                            <th class="py-3.5 px-3">Potongan Lain (Rp)</th>
                            <th class="py-3.5 px-4 text-center">Ikut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @foreach($memberships as $m)
                            @php
                                $u = $m->user;
                                $uid = $u->id;
                                $isDaily = ($m->employment_type === 'daily_worker');
                                $autoLoan = $activeLoans->get($uid);
                                $loanInstal = $autoLoan ? min((float)$autoLoan->remaining_balance, (float)$autoLoan->monthly_installment) : 0;
                                $comm = $earnedCommissions->get($uid, 0);
                            @endphp
                            <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.015] transition">
                                <!-- Karyawan Info -->
                                <td class="py-3.5 px-4 sm:px-5">
                                    <div class="font-bold text-black dark:text-white text-[13.5px]">{{ $u->name }}</div>
                                    <div class="text-[11.5px] text-black/60 dark:text-white/60 font-medium">{{ $m->job_title ?: ucfirst($m->role) }}</div>
                                    
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">
                                            {{ $m->tax_ptkp_status ?: 'TK/0' }}
                                        </span>
                                        @if($m->bpjs_tk_enabled)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">BPJS TK</span>
                                        @endif
                                        @if($m->bpjs_kes_enabled)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#34C759]/10 text-[#34C759]">BPJS Kes</span>
                                        @endif
                                    </div>

                                    <input type="hidden" name="employees[{{ $uid }}][job_title]" value="{{ $m->job_title ?: ucfirst($m->role) }}">
                                    <input type="hidden" name="employees[{{ $uid }}][employment_type]" value="{{ $m->employment_type }}">
                                    <input type="hidden" name="employees[{{ $uid }}][join_date]" value="{{ $m->join_date ? \Carbon\Carbon::parse($m->join_date)->toDateString() : '' }}">
                                    <input type="hidden" name="employees[{{ $uid }}][tax_ptkp_status]" value="{{ $m->tax_ptkp_status }}">
                                    <input type="hidden" name="employees[{{ $uid }}][bpjs_tk_enabled]" value="{{ $m->bpjs_tk_enabled ? 1 : 0 }}">
                                    <input type="hidden" name="employees[{{ $uid }}][bpjs_kes_enabled]" value="{{ $m->bpjs_kes_enabled ? 1 : 0 }}">
                                </td>

                                <!-- Tipe & Pokok -->
                                <td class="py-3.5 px-3">
                                    @if($isDaily)
                                        <div class="text-[11px] text-[#FF9500] font-bold mb-1">Upah Harian:</div>
                                        <input type="number" name="employees[{{ $uid }}][daily_rate]" value="{{ (int)$m->daily_rate }}"
                                            class="w-32 h-9 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                                    @else
                                        <div class="text-[11px] text-[#34C759] font-bold mb-1">Gaji Pokok:</div>
                                        <input type="number" name="employees[{{ $uid }}][base_salary]" value="{{ (int)$m->base_salary }}"
                                            class="w-36 h-9 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                                    @endif
                                </td>

                                <!-- Tunjangan -->
                                <td class="py-3.5 px-3 space-y-1.5">
                                    <div>
                                        <span class="text-[10px] text-black/50 dark:text-white/50 block font-medium">Tetap (Rp):</span>
                                        <input type="number" name="employees[{{ $uid }}][fixed_allowances]" value="{{ (int)$m->fixed_allowances }}" placeholder="Tetap"
                                            class="w-32 h-8.5 px-2 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12.5px] font-medium text-black dark:text-white tabular-nums">
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-black/50 dark:text-white/50 block font-medium">Makan/Transp (Rp):</span>
                                        <input type="number" name="employees[{{ $uid }}][variable_allowances]" value="{{ (int)$m->variable_allowances }}" placeholder="Variabel"
                                            class="w-32 h-8.5 px-2 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12.5px] font-medium text-black dark:text-white tabular-nums">
                                    </div>
                                </td>

                                <!-- Hari Kerja (khusus daily) -->
                                <td class="py-3.5 px-3">
                                    @if($isDaily)
                                        <input type="number" name="employees[{{ $uid }}][days_worked]" value="25" min="1" max="31"
                                            class="w-20 h-9 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                                        <span class="text-[10px] text-black/50 dark:text-white/50 block mt-0.5 text-center">Hari</span>
                                    @else
                                        <div class="px-2.5 py-1 rounded-[7px] bg-black/[0.03] dark:bg-white/[0.05] text-[11px] font-semibold text-black/60 dark:text-white/60 inline-block">
                                            1 Bulan Penuh
                                        </div>
                                    @endif
                                </td>

                                <!-- Lembur -->
                                <td class="py-3.5 px-3">
                                    <input type="number" name="employees[{{ $uid }}][overtime_pay]" value="0" placeholder="0"
                                        class="w-28 h-9 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12.5px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                                </td>

                                <!-- Komisi -->
                                <td class="py-3.5 px-3">
                                    <input type="number" name="employees[{{ $uid }}][commissions]" value="{{ (int)$comm }}" placeholder="0"
                                        class="w-28 h-9 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12.5px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                                    @if($comm > 0)
                                        <div class="text-[10.5px] text-[#34C759] font-bold mt-1 flex items-center gap-1">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Otomatis SPK</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Potongan Kasbon -->
                                <td class="py-3.5 px-3">
                                    <input type="number" name="employees[{{ $uid }}][loan_deduction]" value="{{ (int)$loanInstal }}" placeholder="0"
                                        class="w-32 h-9 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12.5px] font-semibold text-[#FF9500] tabular-nums focus:ring-2 focus:ring-[#FF9500]/50">
                                    @if($autoLoan)
                                        <div class="text-[10.5px] text-[#FF9500] font-medium mt-1">
                                            Sisa: Rp {{ number_format((float)$autoLoan->remaining_balance, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Potongan Lain -->
                                <td class="py-3.5 px-3">
                                    <input type="number" name="employees[{{ $uid }}][other_deductions]" value="0" placeholder="0"
                                        class="w-28 h-9 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12.5px] font-medium text-[#FF3B30] tabular-nums focus:ring-2 focus:ring-[#FF3B30]/50">
                                </td>

                                <!-- Ikut Penggajian -->
                                <td class="py-3.5 px-4 text-center">
                                    <input type="checkbox" name="employees[{{ $uid }}][include]" value="1" checked
                                        class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-0 cursor-pointer">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Catatan Penggajian -->
            <div class="p-4 sm:p-5 border-t border-black/5 dark:border-white/10">
                <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">
                    Catatan Internal Penggajian (Opsional)
                </label>
                <textarea name="notes" rows="2" placeholder="Catatan khusus untuk periode penggajian ini (misal: pembayaran bonus target triwulan)..."
                    class="w-full p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 focus:outline-none"></textarea>
            </div>
        </div>

        <!-- Sticky Floating Action Bar at Bottom -->
        <div class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-8 z-40 max-w-xl mx-auto sm:mx-0">
            <div class="p-3 sm:p-3.5 rounded-[18px] bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/10 dark:border-white/15 shadow-[0_12px_32px_rgba(0,0,0,0.18)] backdrop-blur-xl flex items-center justify-between gap-3">
                <div class="pl-2 hidden sm:block">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">Siap Dihitung</span>
                    <span class="text-[13px] font-bold text-black dark:text-white">{{ $memberships->count() }} Karyawan Aktif</span>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <a href="{{ route('hrm.index', ['tab' => 'payrolls']) }}"
                        class="h-11 px-4.5 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center justify-center">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 sm:flex-initial h-11 px-6 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13.5px] font-bold shadow-[0_4px_16px_rgba(52,199,89,0.35)] transition active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="calculator" class="w-4 h-4"></i>
                        <span>Kalkulasi &amp; Simpan Draf</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
