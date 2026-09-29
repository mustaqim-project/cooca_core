{{--
    FASE 5: Coffee Shop QR Dine-In Order (`artisan_brew` theme)
    PRD-21 §3.1.C.3: QR Meja & Dine-In Ordering
    Auto-detects table number from QR scan URL param (?meja=X)
    Displays dine-in order form when table is detected.
--}}

@php
    $qrTableNumber = request()->query('meja', request()->query('table'));
    $hasQrTable = !empty($qrTableNumber);
@endphp

@if ($hasQrTable || ($isDiningIndustry ?? false))
    <div x-data="{
        tableNumber: '{{ e($qrTableNumber ?? '') }}',
        guestCount: 1,
        isExpanded: {{ $hasQrTable ? 'true' : 'false' }},
    }"
        class="rounded-[20px] overflow-hidden shadow-sm"
        style="border: 1px solid rgba(139, 90, 43, 0.1); background: #FFFFFF;">

        {{-- QR Dine-In Header Bar --}}
        <button @click="isExpanded = !isExpanded" type="button"
            class="w-full flex items-center justify-between p-4 sm:p-5 transition-all duration-200 cursor-pointer"
            style="background: linear-gradient(135deg, #FAF7F2 0%, #F5EDE0 100%);">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] flex items-center justify-center shrink-0"
                    style="background: rgba(139, 90, 43, 0.12);">
                    <i data-lucide="scan-line" class="w-5 h-5" style="color: #8B5A2B;"></i>
                </div>
                <div class="text-left">
                    <h3 class="font-heading font-bold text-sm" style="color: #3D2B1F;">
                        @if ($hasQrTable)
                            <i data-lucide="coffee" class="w-3.5 h-3.5 inline-block mr-0.5" style="color: #8B5A2B;"></i> Pesan di Meja {{ e($qrTableNumber) }}
                        @else
                            Pesan Dine-In di Meja
                        @endif
                    </h3>
                    <p class="text-[11px]" style="color: #8B5A2B99;">
                        @if ($hasQrTable)
                            Scan QR meja terdeteksi — langsung pesan tanpa antre!
                        @else
                            Masukkan nomor meja Anda untuk pesan langsung
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if ($hasQrTable)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold animate-pulse"
                        style="background: #059669; color: #FFFFFF;">
                        <i data-lucide="check" class="w-3 h-3"></i>
                        <span>Terdeteksi</span>
                    </span>
                @endif
                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                    :class="isExpanded ? 'rotate-180' : ''"
                    style="color: #8B5A2B;"></i>
            </div>
        </button>

        {{-- Expanded Dine-In Order Form --}}
        <div x-show="isExpanded" x-collapse
            class="p-4 sm:p-5 space-y-4"
            style="border-top: 1px solid rgba(139, 90, 43, 0.08);">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Table Number Input --}}
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold uppercase tracking-wider" style="color: #8B5A2B;">
                        Nomor Meja
                    </label>
                    <div class="relative">
                        <i data-lucide="hash" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2" style="color: #C88A58;"></i>
                        <input type="text" x-model="tableNumber"
                            placeholder="Contoh: 5"
                            class="w-full pl-9 pr-4 py-2.5 rounded-[12px] text-sm font-semibold focus:outline-none focus:ring-2 transition"
                            style="border: 1.5px solid rgba(139, 90, 43, 0.15); background: #FAF7F2; color: #3D2B1F; --tw-ring-color: #8B5A2B;"
                            {{ $hasQrTable ? 'readonly' : '' }}>
                    </div>
                </div>

                {{-- Guest Count --}}
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold uppercase tracking-wider" style="color: #8B5A2B;">
                        Jumlah Tamu
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="guestCount = Math.max(1, guestCount - 1)"
                            class="w-10 h-10 rounded-[10px] flex items-center justify-center transition active:scale-95"
                            style="background: rgba(139, 90, 43, 0.08); color: #8B5A2B;">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        <span class="flex-1 text-center py-2 rounded-[10px] text-sm font-bold"
                            style="background: #FAF7F2; color: #3D2B1F; font-variant-numeric: tabular-nums; border: 1px solid rgba(139, 90, 43, 0.1);"
                            x-text="guestCount + ' orang'"></span>
                        <button type="button" @click="guestCount = Math.min(20, guestCount + 1)"
                            class="w-10 h-10 rounded-[10px] flex items-center justify-center transition active:scale-95"
                            style="background: rgba(139, 90, 43, 0.08); color: #8B5A2B;">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table info note --}}
            <div class="flex items-start gap-2 p-3 rounded-[12px]"
                style="background: rgba(139, 90, 43, 0.04); border: 1px solid rgba(139, 90, 43, 0.08);">
                <i data-lucide="info" class="w-3.5 h-3.5 mt-0.5 shrink-0" style="color: #C88A58;"></i>
                <p class="text-[11px] leading-relaxed" style="color: #6B5744;">
                    Nomor meja akan otomatis terisi saat Anda scan QR code yang ada di meja. Tambahkan item kopi ke keranjang lalu checkout — pesanan langsung masuk ke barista.
                </p>
            </div>

            {{-- Hidden fields for checkout --}}
            <input type="hidden" name="dine_in_table" :value="tableNumber">
            <input type="hidden" name="dine_in_guests" :value="guestCount">
        </div>
    </div>
@endif
