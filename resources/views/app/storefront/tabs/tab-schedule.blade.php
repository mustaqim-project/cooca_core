{{-- TAB 4: JAM OPERASIONAL & SLOT WAKTU --}}
@php
    $dayNames = [
        'monday'    => 'Senin',
        'tuesday'   => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday'  => 'Kamis',
        'friday'    => 'Jumat',
        'saturday'  => 'Sabtu',
        'sunday'    => 'Minggu',
    ];
    $activeDays = is_array($setting->operating_days) ? $setting->operating_days : array_keys($dayNames);

    $slotList = '';
    if (! empty($setting->available_slots) && is_array($setting->available_slots)) {
        $slotList = implode("\n", $setting->available_slots);
    }

    $customBatchText = '';
    if (! empty($setting->custom_batch_dates) && is_array($setting->custom_batch_dates)) {
        $lines = [];
        foreach ($setting->custom_batch_dates as $cbd) {
            $dateStr = $cbd['date'] ?? '';
            if ($dateStr !== '') {
                $quotaPart = isset($cbd['quota']) && $cbd['quota'] !== null ? " : {$cbd['quota']}" : '';
                $notePart = ! empty($cbd['note']) ? " : {$cbd['note']}" : '';
                $lines[] = "{$dateStr}{$quotaPart}{$notePart}";
            }
        }
        $customBatchText = implode("\n", $lines);
    }
@endphp

<div class="space-y-6" x-data="{ batchMode: '{{ $setting->batch_dates_mode ?? 'operating_days' }}' }">
    {{-- HARI OPERASIONAL & SLOT --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                <i data-lucide="clock" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">Hari &amp; Slot Waktu Layanan</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Hari buka toko untuk pengantaran, pengambilan pickup, dan reservasi</p>
            </div>
        </div>

        <div>
            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2.5">
                Pilih Hari Operasional Buka Toko
            </label>
            <div class="flex flex-wrap gap-2">
                @foreach ($dayNames as $dKey => $dLabel)
                    <label class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 text-[12.5px] font-medium text-black dark:text-white cursor-pointer hover:border-[#007AFF] transition select-none">
                        <input type="checkbox" name="operating_days[]" value="{{ $dKey }}"
                            class="rounded border-gray-300 text-[#007AFF] focus:ring-[#007AFF]"
                            {{ in_array($dKey, $activeDays, true) ? 'checked' : '' }}>
                        <span>{{ $dLabel }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                Daftar Slot Jam Layanan / Pengantaran (1 baris per slot)
            </label>
            <textarea name="available_slots" rows="3"
                class="w-full p-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"
                placeholder="09:00 - 11:00&#10;11:00 - 13:00&#10;14:00 - 16:00&#10;16:00 - 18:00">{{ $slotList }}</textarea>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Format: Jam Mulai - Jam Selesai (contoh: 10:00 - 12:00).</p>
        </div>
    </div>

    {{-- ATURAN PRE-ORDER & LEAD TIME --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="w-9 h-9 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold">
                <i data-lucide="timer" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">Kapasitas &amp; Lead Time Pemesanan</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Pengaturan jam persiapan dan batas kuota pesanan pre-order</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Lead Time Persiapan (Jam)
                </label>
                <input type="number" name="lead_time_hours" value="{{ $setting->lead_time_hours ?? 0 }}" min="0" max="720"
                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Waktu minimal sebelum pesanan siap (misal: 24 jam untuk H-1).</p>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Jam Batas Pemesanan (Cut-Off)
                </label>
                <input type="time" name="cut_off_time" value="{{ $setting->cut_off_time }}"
                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Batas jam harian untuk pengantaran hari berikutnya.</p>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Batas Kuota Harian
                </label>
                <input type="number" name="daily_order_quota" value="{{ $setting->daily_order_quota ?? 0 }}" min="0" max="10000"
                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Isi 0 jika tidak membatasi kuota order per hari.</p>
            </div>
        </div>

        {{-- MODE TANGGAL BATCH (KATERING & PO) --}}
        <div class="pt-2 border-t border-black/5 dark:border-white/10 space-y-3">
            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                Mode Pemilihan Tanggal Pesanan
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex items-start gap-3 p-3.5 rounded-[14px] border cursor-pointer transition"
                    :class="batchMode === 'operating_days' ? 'bg-[#007AFF]/5 border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/10'">
                    <input type="radio" name="batch_dates_mode" value="operating_days" x-model="batchMode"
                        class="mt-1 text-[#007AFF] focus:ring-[#007AFF]">
                    <div>
                        <span class="text-[13px] font-bold text-black dark:text-white block">Hari Operasional Reguler</span>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">Pelanggan dapat memilih tanggal bebas sesuai hari buka toko.</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3.5 rounded-[14px] border cursor-pointer transition"
                    :class="batchMode === 'custom_dates' ? 'bg-[#007AFF]/5 border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/10'">
                    <input type="radio" name="batch_dates_mode" value="custom_dates" x-model="batchMode"
                        class="mt-1 text-[#007AFF] focus:ring-[#007AFF]">
                    <div>
                        <span class="text-[13px] font-bold text-black dark:text-white block">Tanggal Batch Tertentu (Pre-Order Spesifik)</span>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">Hanya tanggal-tanggal spesifik tertentu yang dapat dipilih.</p>
                    </div>
                </label>
            </div>

            <div x-show="batchMode === 'custom_dates'" x-cloak class="pt-2">
                <label class="block text-[11.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Daftar Tanggal Batch Spesifik (1 baris per tanggal)
                </label>
                <textarea name="custom_batch_dates" rows="3"
                    class="w-full p-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"
                    placeholder="2026-10-01 : 150 : Batch 1 Jumat&#10;2026-10-08 : 150 : Batch 2 Jumat">{{ $customBatchText }}</textarea>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Format: TTTT-BB-HH : Kuota : Catatan (opsional).</p>
            </div>
        </div>
    </div>
</div>
