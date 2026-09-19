@extends('layouts.app', [
    'title' => $payroll->title,
    'headerTitle' => $payroll->title,
    'headerSubtitle' => 'Detail batch penggajian periode ' . $payroll->formatted_period . ' (' . $payroll->total_employees_count . ' karyawan).'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-16" x-data="{
    showPayModal: false,
    paymentMethod: 'bank_transfer'
}">

    <!-- Top Navigation & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('hrm.index', ['tab' => 'payrolls']) }}"
            class="inline-flex items-center gap-1.5 text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Hub Penggajian</span>
        </a>

        <!-- Action Workflow Buttons -->
        <div class="flex items-center gap-2">
            @if($payroll->status === 'draft')
                @if(\App\Support\Context::hasPermission('users.manage'))
                    <form method="POST" action="{{ route('hrm.payrolls.approve', $payroll->id) }}">
                        @csrf
                        <button type="submit"
                            class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-bold shadow-xs transition active:scale-[0.97] flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>Setujui Penggajian (Approve)</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('hrm.payrolls.destroy', $payroll->id) }}"
                        onsubmit="return confirm('Hapus draf penggajian ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="h-9 px-3 rounded-[10px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 text-[12px] font-semibold transition active:scale-[0.97] flex items-center gap-1 cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Hapus Draf</span>
                        </button>
                    </form>
                @endif
            @elseif($payroll->status === 'approved')
                @if(\App\Support\Context::hasPermission('users.manage'))
                    <button type="button" @click="showPayModal = true"
                        class="h-9 px-4.5 rounded-[10px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[12px] font-bold shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.97] flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Tandai Telah Dibayar (Mark Paid)</span>
                    </button>
                @endif
            @else
                <div class="h-9 px-3.5 rounded-[10px] bg-[#34C759]/15 text-[#34C759] text-[12px] font-bold flex items-center gap-1.5 border border-[#34C759]/30">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Telah Dibayar pada {{ $payroll->paid_at ? $payroll->paid_at->translatedFormat('d M Y H:i') : 'Selesai' }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Bento Financial Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Gaji Bersih (THP) -->
        <div class="p-4 sm:p-5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs space-y-2">
            <div class="text-[12px] font-medium uppercase tracking-wider text-black/50 dark:text-white/50">Total Gaji Bersih (THP)</div>
            <div class="text-[24px] sm:text-[26px] font-bold text-[#34C759] tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)$payroll->total_take_home_pay, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45">Dana ditransfer ke karyawan</p>
        </div>

        <!-- Total Beban Perusahaan -->
        <div class="p-4 sm:p-5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs space-y-2">
            <div class="text-[12px] font-medium uppercase tracking-wider text-black/50 dark:text-white/50">Total Beban Usaha</div>
            <div class="text-[24px] sm:text-[26px] font-bold text-black dark:text-white tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)$payroll->total_company_cost, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45">Termasuk premi BPJS kantor</p>
        </div>

        <!-- Total Iuran BPJS -->
        <div class="p-4 sm:p-5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs space-y-2">
            <div class="text-[12px] font-medium uppercase tracking-wider text-black/50 dark:text-white/50">Total Iuran BPJS</div>
            <div class="text-[22px] sm:text-[24px] font-bold text-[#007AFF] tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)($payroll->total_bpjs_company + $payroll->total_bpjs_employee), 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45">
                Kantor: Rp {{ number_format((float)$payroll->total_bpjs_company, 0, ',', '.') }}
            </p>
        </div>

        <!-- Setoran Pajak PPh 21 TER -->
        <div class="p-4 sm:p-5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs space-y-2">
            <div class="text-[12px] font-medium uppercase tracking-wider text-black/50 dark:text-white/50">Pajak PPh 21 TER</div>
            <div class="text-[22px] sm:text-[24px] font-bold text-[#AF52DE] tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)$payroll->total_pph21, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45">Disetor ke kas negara (DJP)</p>
        </div>
    </div>

    <!-- Tabel Daftar Slip Gaji Tiap Karyawan -->
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs overflow-hidden space-y-2">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Daftar Slip Gaji Karyawan</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Klik tombol aksi untuk melihat atau membagikan slip gaji digital via WhatsApp.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[12px] text-black/60 dark:text-white/60">Status:</span>
                @if($payroll->status === 'paid')
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">Dibayar (Paid)</span>
                @elseif($payroll->status === 'approved')
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]">Disetujui (Approved)</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">Draft</span>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.015] dark:bg-white/[0.02] text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                        <th class="py-3 px-4 sm:px-5">Karyawan</th>
                        <th class="py-3 px-3">Gaji Pokok / Upah</th>
                        <th class="py-3 px-3">Tunjangan &amp; Komisi</th>
                        <th class="py-3 px-3">Bruto</th>
                        <th class="py-3 px-3">BPJS &amp; Pajak</th>
                        <th class="py-3 px-3">Potongan Kasbon</th>
                        <th class="py-3 px-3">Gaji Bersih (THP)</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
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
                                <div class="font-semibold text-black dark:text-white">{{ $item->employee_name }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">{{ $item->job_title ?: 'Staf' }}</div>
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-black dark:text-white">
                                @if($item->employment_type === 'daily_worker')
                                    <div>Rp {{ number_format((float)$item->daily_rate, 0, ',', '.') }} x {{ $item->days_worked }} hr</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">Harian</div>
                                @else
                                    <div>Rp {{ number_format((float)$item->base_salary, 0, ',', '.') }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">Tetap/Kontrak</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-black/70 dark:text-white/70">
                                Rp {{ number_format((float)($item->fixed_allowances + $item->variable_allowances + $item->commissions + $item->overtime_pay), 0, ',', '.') }}
                                @if($item->thr_amount > 0)
                                    <div class="text-[10px] text-[#34C759] font-semibold">+ THR: Rp {{ number_format((float)$item->thr_amount, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 tabular-nums font-semibold text-black dark:text-white">
                                Rp {{ number_format((float)$item->gross_pay, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-[#FF3B30]">
                                -Rp {{ number_format((float)($item->bpjs_tk_employee + $item->bpjs_kes_employee + $item->pph21_amount), 0, ',', '.') }}
                                <div class="text-[10px] text-black/40 dark:text-white/40 font-normal">
                                    BPJS: {{ number_format((float)($item->bpjs_tk_employee + $item->bpjs_kes_employee), 0, ',', '.') }} | Pajak: {{ number_format((float)$item->pph21_amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-[#FF9500]">
                                @if($item->loan_deduction > 0)
                                    -Rp {{ number_format((float)$item->loan_deduction, 0, ',', '.') }}
                                @else
                                    <span class="text-[11px] text-black/35 dark:text-white/35">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 tabular-nums font-bold text-[#34C759]">
                                Rp {{ number_format((float)$item->take_home_pay, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Tombol Slip Digital -->
                                    <a href="{{ route('hrm.payslips.show', $item->id) }}"
                                        class="h-7.5 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition flex items-center gap-1">
                                        <i data-lucide="receipt" class="w-3 h-3"></i>
                                        <span>Lihat Slip</span>
                                    </a>

                                    <!-- Tombol WhatsApp -->
                                    @if($wa)
                                        <a href="https://wa.me/{{ $wa }}?text={{ $waText }}" target="_blank"
                                            class="h-7.5 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#25D366] bg-[#25D366]/10 hover:bg-[#25D366]/15 active:scale-[0.97] transition flex items-center gap-1"
                                            title="Kirim slip gaji via WhatsApp">
                                            <i data-lucide="send" class="w-3 h-3"></i>
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

    <!-- MODAL PEMBAYARAN GAJI (MARK AS PAID) -->
    <div x-show="showPayModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;">
        <div @click.outside="showPayModal = false" class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.2)] overflow-hidden">
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">Bayar Penggajian Karyawan</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Pengeluaran kas dan potongan kasbon akan otomatis dibukukan.</p>
                </div>
                <button type="button" @click="showPayModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.payrolls.pay', $payroll->id) }}" class="p-5 space-y-4">
                @csrf
                <div class="p-3 rounded-[12px] bg-[#34C759]/10 text-[#34C759] text-[13px] font-bold text-center">
                    Total Dana Dibayarkan: Rp {{ number_format((float)$payroll->total_take_home_pay, 0, ',', '.') }}
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Metode Pembayaran <span class="text-[#FF3B30]">*</span></label>
                    <select name="payment_method" x-model="paymentMethod" required class="w-full h-10 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="bank_transfer">Transfer Bank (BCA / Mandiri / dll)</option>
                        <option value="cash">Tunai / Uang Laci Kasir</option>
                        <option value="multi">Campuran (Multi-Channel)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Catatan Pembayaran (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Contoh: Ditransfer via Corporate Internet Banking..."
                        class="w-full p-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border-none text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showPayModal = false"
                        class="h-9 px-4 rounded-[9px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-9 px-4.5 rounded-[9px] text-[12px] font-bold text-white bg-[#34C759] hover:bg-[#2FB34F] shadow-xs transition active:scale-[0.97] cursor-pointer">
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
