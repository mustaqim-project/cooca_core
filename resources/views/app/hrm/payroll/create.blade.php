@extends('layouts.app', [
    'title' => 'Buat Penggajian Bulanan',
    'headerTitle' => 'Buat Penggajian Bulanan',
    'headerSubtitle' => 'Kalkulasi batch gaji karyawan, potongan BPJS, PPh 21 TER, dan kasbon untuk periode berjalan.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-16" x-data="{
    periodMonth: {{ $month }},
    periodYear: {{ $year }},
    includeThr: false,
    formatRupiah(val) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
    }
}">

    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('hrm.index', ['tab' => 'payrolls']) }}"
            class="inline-flex items-center gap-1.5 text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Hub Penggajian</span>
        </a>
    </div>

    <form method="POST" action="{{ route('hrm.payrolls.store') }}" class="space-y-6">
        @csrf

        <!-- Bento Card: Pengaturan Periode & Opsi Tambahan -->
        <div class="p-5 sm:p-6 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs space-y-4">
            <div class="border-b border-black/5 dark:border-white/5 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-[16px] font-semibold text-black dark:text-white">Konfigurasi Periode Penggajian</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Tentukan bulan kerja dan sertakan opsi pembayaran THR jika bertepatan hari raya.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                <!-- Pilihan Bulan -->
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Bulan Penggajian <span class="text-[#FF3B30]">*</span></label>
                    <select name="period_month" x-model="periodMonth" required
                        class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $mNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                            @endphp
                            <option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>{{ $mNames[$m] }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Pilihan Tahun -->
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Tahun <span class="text-[#FF3B30]">*</span></label>
                    <input type="number" name="period_year" x-model="periodYear" required min="2020" max="2050" value="{{ $year }}"
                        class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <!-- Opsi THR Prorata Join Date -->
                <div class="pt-5">
                    <label class="p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center gap-3 cursor-pointer hover:bg-black/[0.05] transition">
                        <input type="checkbox" name="include_thr" value="1" x-model="includeThr"
                            class="w-4.5 h-4.5 rounded text-[#007AFF] focus:ring-0">
                        <div>
                            <span class="text-[13px] font-semibold text-black dark:text-white block">Sertakan THR Prorata</span>
                            <span class="text-[11px] text-black/50 dark:text-white/50 block leading-tight">Sesuai Permenaker 6/2016 (otomatis berdasar tanggal bergabung)</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Tabel Masukan Variabel Gaji per Karyawan -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs overflow-hidden space-y-2">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Rincian Komponen Upah &amp; Variabel Karyawan</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Sesuaikan lembur, hari kerja pekerja harian, dan verifikasi potongan kasbon sebelum kalkulasi final.</p>
                </div>
                <span class="text-[12px] px-2.5 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] font-bold">
                    {{ $memberships->count() }} Karyawan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[12px] border-collapse min-w-[900px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.015] dark:bg-white/[0.02] text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                            <th class="py-3 px-4">Karyawan</th>
                            <th class="py-3 px-3">Tipe &amp; Pokok (Rp)</th>
                            <th class="py-3 px-3">Tunjangan (Rp)</th>
                            <th class="py-3 px-3">Hari Kerja</th>
                            <th class="py-3 px-3">Lembur (Rp)</th>
                            <th class="py-3 px-3">Komisi (Rp)</th>
                            <th class="py-3 px-3">Potongan Kasbon (Rp)</th>
                            <th class="py-3 px-3">Potongan Lain (Rp)</th>
                            <th class="py-3 px-4 text-center">Ikut</th>
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
                                <!-- Karyawan -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-black dark:text-white">{{ $u->name }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">{{ $m->job_title ?: ucfirst($m->role) }}</div>
                                    <input type="hidden" name="employees[{{ $uid }}][job_title]" value="{{ $m->job_title ?: ucfirst($m->role) }}">
                                    <input type="hidden" name="employees[{{ $uid }}][employment_type]" value="{{ $m->employment_type }}">
                                    <input type="hidden" name="employees[{{ $uid }}][join_date]" value="{{ $m->join_date ? \Carbon\Carbon::parse($m->join_date)->toDateString() : '' }}">
                                    <input type="hidden" name="employees[{{ $uid }}][tax_ptkp_status]" value="{{ $m->tax_ptkp_status }}">
                                    <input type="hidden" name="employees[{{ $uid }}][bpjs_tk_enabled]" value="{{ $m->bpjs_tk_enabled ? 1 : 0 }}">
                                    <input type="hidden" name="employees[{{ $uid }}][bpjs_kes_enabled]" value="{{ $m->bpjs_kes_enabled ? 1 : 0 }}">
                                </td>

                                <!-- Tipe & Pokok -->
                                <td class="py-3 px-3">
                                    @if($isDaily)
                                        <div class="text-[11px] text-[#FF9500] font-semibold">Harian</div>
                                        <input type="number" name="employees[{{ $uid }}][daily_rate]" value="{{ (int)$m->daily_rate }}"
                                            class="w-28 h-8 px-2 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white tabular-nums">
                                    @else
                                        <div class="text-[11px] text-[#34C759] font-semibold">Bulanan</div>
                                        <input type="number" name="employees[{{ $uid }}][base_salary]" value="{{ (int)$m->base_salary }}"
                                            class="w-32 h-8 px-2 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white tabular-nums">
                                    @endif
                                </td>

                                <!-- Tunjangan -->
                                <td class="py-3 px-3">
                                    <input type="number" name="employees[{{ $uid }}][fixed_allowances]" value="{{ (int)$m->fixed_allowances }}" placeholder="Tetap"
                                        class="w-28 h-8 px-2 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white tabular-nums mb-1 block">
                                    <input type="number" name="employees[{{ $uid }}][variable_allowances]" value="{{ (int)$m->variable_allowances }}" placeholder="Makan/Transp"
                                        class="w-28 h-8 px-2 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white tabular-nums block">
                                </td>

                                <!-- Hari Kerja (khusus daily) -->
                                <td class="py-3 px-3">
                                    @if($isDaily)
                                        <input type="number" name="employees[{{ $uid }}][days_worked]" value="25" min="1" max="31"
                                            class="w-16 h-8 px-2 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white tabular-nums">
                                    @else
                                        <span class="text-[11px] text-black/40 dark:text-white/40">Full (1 Bln)</span>
                                    @endif
                                </td>

                                <!-- Lembur -->
                                <td class="py-3 px-3">
                                    <input type="number" name="employees[{{ $uid }}][overtime_pay]" value="0" placeholder="0"
                                        class="w-24 h-8 px-2 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white tabular-nums">
                                </td>

                                <!-- Komisi -->
                                <td class="py-3 px-3">
                                    <input type="number" name="employees[{{ $uid }}][commissions]" value="{{ (int)$comm }}" placeholder="0"
                                        class="w-24 h-8 px-2 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white tabular-nums">
                                    @if($comm > 0)
                                        <span class="text-[10px] text-[#34C759] font-medium block mt-0.5">Auto dari SPK</span>
                                    @endif
                                </td>

                                <!-- Potongan Kasbon -->
                                <td class="py-3 px-3">
                                    <input type="number" name="employees[{{ $uid }}][loan_deduction]" value="{{ (int)$loanInstal }}" placeholder="0"
                                        class="w-28 h-8 px-2 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white tabular-nums">
                                    @if($autoLoan)
                                        <span class="text-[10px] text-[#FF9500] font-medium block mt-0.5">Sisa: Rp {{ number_format((float)$autoLoan->remaining_balance, 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                <!-- Potongan Lain -->
                                <td class="py-3 px-3">
                                    <input type="number" name="employees[{{ $uid }}][other_deductions]" value="0" placeholder="0"
                                        class="w-24 h-8 px-2 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[12px] text-black dark:text-white tabular-nums">
                                </td>

                                <!-- Ikut Penggajian -->
                                <td class="py-3 px-4 text-center">
                                    <input type="checkbox" name="employees[{{ $uid }}][include]" value="1" checked
                                        class="w-4 h-4 rounded text-[#007AFF] focus:ring-0">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Catatan Penggajian -->
            <div class="p-4 sm:p-5 border-t border-black/5 dark:border-white/5">
                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Catatan Tambahan untuk Periode Ini</label>
                <textarea name="notes" rows="2" placeholder="Catatan internal pengelola keuangan..."
                    class="w-full p-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('hrm.index', ['tab' => 'payrolls']) }}"
                class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center justify-center">
                Batal
            </a>
            <button type="submit"
                class="h-11 px-6 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.97] flex items-center gap-2 cursor-pointer">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                <span>Kalkulasi &amp; Simpan Draf Penggajian</span>
            </button>
        </div>
    </form>
</div>
@endsection
