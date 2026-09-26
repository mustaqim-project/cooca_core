@extends('layouts.app', [
    'title' => $payroll->title,
    'headerTitle' => $payroll->title,
    'headerSubtitle' => 'Detail batch penggajian periode ' . $payroll->formatted_period . ' (' . $payroll->total_employees_count . ' karyawan).'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-20" x-data="{
    showPayModal: false,
    paymentMethod: 'bank_transfer'
}">

    <!-- Top Navigation & Action Workflow Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('hrm.index', ['tab' => 'payrolls']) }}"
            class="inline-flex items-center gap-2 text-[13px] font-semibold text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white transition group">
            <div class="w-8 h-8 rounded-[10px] bg-black/5 dark:bg-white/10 flex items-center justify-center group-hover:bg-black/10 dark:group-hover:bg-white/15 transition">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/70 dark:text-white/70"></i>
            </div>
            <span>Kembali ke Hub Penggajian</span>
        </a>

        <!-- Action Workflow Buttons -->
        <div class="flex items-center gap-2">
            @if($payroll->status === 'draft')
                @if(\App\Support\Context::hasPermission('users.manage'))
                    <form method="POST" action="{{ route('hrm.payrolls.approve', $payroll->id) }}">
                        @csrf
                        <button type="submit"
                            class="h-10 px-4.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold shadow-xs transition active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>Setujui Penggajian (Approve)</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('hrm.payrolls.destroy', $payroll->id) }}"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus draf penggajian ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="h-10 px-3.5 rounded-[12px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 text-[12.5px] font-bold transition active:scale-[0.98] flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Hapus Draf</span>
                        </button>
                    </form>
                @endif
            @elseif($payroll->status === 'approved')
                @if(\App\Support\Context::hasPermission('users.manage'))
                    <button type="button" @click="showPayModal = true"
                        class="h-10 px-5 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold shadow-[0_4px_16px_rgba(52,199,89,0.3)] transition active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                        <i data-lucide="check-circle-2" class="w-4.5 h-4.5"></i>
                        <span>Tandai Telah Dibayar (Mark Paid)</span>
                    </button>
                @endif
            @else
                <div class="h-10 px-4 rounded-[12px] bg-[#34C759]/15 text-[#34C759] text-[12.5px] font-bold flex items-center gap-2 border border-[#34C759]/30">
                    <i data-lucide="check-circle" class="w-4.5 h-4.5"></i>
                    <span>Telah Dibayar pada {{ $payroll->paid_at ? $payroll->paid_at->translatedFormat('d M Y H:i') : 'Selesai' }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Bento Financial Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Gaji Bersih (THP) -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">Total Gaji Bersih (THP)</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[24px] sm:text-[26px] font-extrabold text-[#34C759] tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)$payroll->total_take_home_pay, 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">Dana ditransfer ke rekening karyawan</p>
        </div>

        <!-- Total Beban Usaha Perusahaan -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">Total Beban Usaha</span>
                <div class="w-8 h-8 rounded-[9px] bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 flex items-center justify-center">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[24px] sm:text-[26px] font-extrabold text-black dark:text-white tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)$payroll->total_company_cost, 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">Termasuk premi BPJS ditanggung kantor</p>
        </div>

        <!-- Total Iuran BPJS -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">Total Iuran BPJS</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[22px] sm:text-[24px] font-extrabold text-[#007AFF] tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)($payroll->total_bpjs_company + $payroll->total_bpjs_employee), 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">
                Porsi Kantor: Rp {{ number_format((float)$payroll->total_bpjs_company, 0, ',', '.') }}
            </p>
        </div>

        <!-- Setoran Pajak PPh 21 TER -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">Pajak PPh 21 TER</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[22px] sm:text-[24px] font-extrabold text-[#AF52DE] tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)$payroll->total_pph21, 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">Disetor ke kas negara (PP 58/2023)</p>
        </div>
    </div>

    <!-- Tabel Daftar Slip Gaji Tiap Karyawan -->
    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden space-y-2">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Rincian Slip Gaji Karyawan</h3>
                    <p class="text-[12px] text-black/60 dark:text-white/60">Klik tombol aksi untuk melihat slip resmi atau kirim langsung ke WhatsApp staf.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[12px] font-bold text-black/60 dark:text-white/60">Status Batch:</span>
                @if($payroll->status === 'paid')
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30">Lunas / Dibayar</span>
                @elseif($payroll->status === 'approved')
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30">Disetujui</span>
                @else
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30">Draf</span>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse min-w-[980px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                        <th class="py-3.5 px-4 sm:px-5">Karyawan</th>
                        <th class="py-3.5 px-3">Gaji Pokok / Upah</th>
                        <th class="py-3.5 px-3">Tunjangan &amp; Komisi</th>
                        <th class="py-3.5 px-3">Bruto</th>
                        <th class="py-3.5 px-3">BPJS &amp; Pajak</th>
                        <th class="py-3.5 px-3">Potongan Kasbon</th>
                        <th class="py-3.5 px-3">Gaji Bersih (THP)</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @foreach($payroll->items as $item)
                        @php
                            $wa = preg_replace('/[^0-9]/', '', (string)$item->whatsapp_number);
                            if (str_starts_with($wa, '0')) {
                                $wa = '62' . substr($wa, 1);
                            }
                            $service = app(\App\Domain\HRM\PayrollRunService::class);
                            $waText = urlencode($service->buildWhatsAppSlipMessage($item));
                        @endphp
                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.015] transition">
                            <td class="py-3.5 px-4 sm:px-5">
                                <div class="font-bold text-black dark:text-white text-[13.5px]">{{ $item->employee_name }}</div>
                                <div class="text-[11.5px] text-black/60 dark:text-white/60 font-medium">{{ $item->job_title ?: 'Staf' }}</div>
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-black dark:text-white">
                                @if($item->employment_type === 'daily_worker')
                                    <div class="font-semibold">Rp {{ number_format((float)$item->daily_rate, 0, ',', '.') }} x {{ $item->days_worked }} hr</div>
                                    <div class="text-[11px] text-[#FF9500] font-bold">Pekerja Harian</div>
                                @else
                                    <div class="font-semibold">Rp {{ number_format((float)$item->base_salary, 0, ',', '.') }}</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 font-medium">Bulanan</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-black/80 dark:text-white/80 font-medium">
                                Rp {{ number_format((float)($item->fixed_allowances + $item->variable_allowances + $item->commissions + $item->overtime_pay), 0, ',', '.') }}
                                @if($item->thr_amount > 0)
                                    <div class="text-[10.5px] text-[#34C759] font-bold">+ THR: Rp {{ number_format((float)$item->thr_amount, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 tabular-nums font-bold text-black dark:text-white">
                                Rp {{ number_format((float)$item->gross_pay, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-[#FF3B30] font-semibold">
                                -Rp {{ number_format((float)($item->bpjs_tk_employee + $item->bpjs_kes_employee + $item->pph21_amount), 0, ',', '.') }}
                                <div class="text-[10.5px] text-black/50 dark:text-white/50 font-normal">
                                    BPJS: {{ number_format((float)($item->bpjs_tk_employee + $item->bpjs_kes_employee), 0, ',', '.') }} | Pajak: {{ number_format((float)$item->pph21_amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-[#FF9500] font-semibold">
                                @if($item->loan_deduction > 0)
                                    -Rp {{ number_format((float)$item->loan_deduction, 0, ',', '.') }}
                                @else
                                    <span class="text-[11px] text-black/35 dark:text-white/35 font-normal">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 tabular-nums font-extrabold text-[#34C759] text-[14px]">
                                Rp {{ number_format((float)$item->take_home_pay, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Tombol Slip Digital -->
                                    <a href="{{ route('hrm.payslips.show', $item->id) }}"
                                        class="h-8 px-3 rounded-[9px] text-[11.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                        <span>Lihat Slip</span>
                                    </a>

                                    <!-- Tombol WhatsApp -->
                                    @if($wa)
                                        <a href="https://wa.me/{{ $wa }}?text={{ $waText }}" target="_blank"
                                            class="h-8 px-3 rounded-[9px] text-[11.5px] font-bold text-[#25D366] bg-[#25D366]/10 hover:bg-[#25D366]/15 active:scale-[0.97] transition flex items-center gap-1.5 cursor-pointer"
                                            title="Kirim slip gaji via WhatsApp">
                                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                            <span>WhatsApp</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL PEMBAYARAN GAJI (MARK AS PAID) - BENTO APPLE HIG -->
    <div x-show="showPayModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
        <div @click.outside="showPayModal = false" class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Konfirmasi Pembayaran Penggajian</h3>
                        <p class="text-[12px] text-black/60 dark:text-white/60">Pengeluaran kas dan potongan kasbon otomatis dijurnal.</p>
                    </div>
                </div>
                <button type="button" @click="showPayModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.payrolls.pay', $payroll->id) }}" class="p-5 space-y-4 overflow-y-auto">
                @csrf
                <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-center space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#34C759] block">Total Dana Ditransfer</span>
                    <div class="text-[26px] font-black text-[#34C759] tabular-nums tracking-tight">
                        Rp {{ number_format((float)$payroll->total_take_home_pay, 0, ',', '.') }}
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">
                        Metode Pembayaran <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="payment_method" x-model="paymentMethod" required
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="bank_transfer">Transfer Bank (BCA / Mandiri / BRI / dll)</option>
                        <option value="cash">Tunai / Uang Laci Kasir Operasional</option>
                        <option value="multi">Campuran (Multi-Channel)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">
                        Catatan Pembayaran (Opsional)
                    </label>
                    <textarea name="notes" rows="2" placeholder="Contoh: Ditransfer via Corporate Internet Banking No. Ref: PAY-202609..."
                        class="w-full p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showPayModal = false"
                        class="h-10 px-4.5 rounded-[11px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-10 px-5 rounded-[11px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#2FB34F] shadow-[0_4px_14px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                        Konfirmasi Pembayaran Lunas
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
