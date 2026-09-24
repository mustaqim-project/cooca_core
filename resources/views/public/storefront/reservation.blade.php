@extends('public.storefront.layouts.app')

@php
    $resTitle = 'Reservasi Meja & Layanan | ' . $business->name;
    $resDesc = 'Booking meja makan, reservasi jadwal treatment atau konsultasi di ' . $business->name . ' secara praktis. Konfirmasi instan dan tanpa antre panjang.';
    $resCanonical = url('/' . $business->slug . '/reservasi');
    $resOgImage = $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: ($business->logo_url ?: asset('assets/seo/cooca-og-default.jpg')));
@endphp

@section('title', $resTitle)
@section('description', $resDesc)
@section('canonical', $resCanonical)
@section('og_title', 'Reservasi Online - ' . $business->name)
@section('og_description', $resDesc)
@section('og_image', $resOgImage)
@section('og_type', 'website')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "WebPage",
  "name": "{{ addslashes($resTitle) }}",
  "description": "{{ addslashes($resDesc) }}",
  "url": "{{ $resCanonical }}",
  "publisher": {
    "@type": "Organization",
    "name": "{{ addslashes($business->name) }}"
  }
}
</script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 space-y-10"
     x-data="{
         customerName: '{{ auth('customer')->user()?->name ?? '' }}',
         customerPhone: '{{ auth('customer')->user()?->phone ?? '' }}',
         customerEmail: '{{ auth('customer')->user()?->email ?? '' }}',
         reservationDate: '{{ date('Y-m-d') }}',
         timeSlot: '12:00',
         guestCount: 2,
         posTableId: '',
         productId: '',
         notes: '',
         isSubmitting: false,
         successData: null,
         errorMessage: '',

         async submitReservation() {
             if (!this.customerName || !this.customerPhone || !this.reservationDate || !this.timeSlot) {
                 alert('Nama, nomor WhatsApp, tanggal, dan jam wajib diisi.');
                 return;
             }

             this.isSubmitting = true;
             this.errorMessage = '';

             const payload = {
                 _token: '{{ csrf_token() }}',
                 customer_name: this.customerName,
                 customer_phone: this.customerPhone,
                 customer_email: this.customerEmail,
                 reservation_date: this.reservationDate,
                 time_slot: this.timeSlot,
                 guest_count: this.guestCount,
                 pos_table_id: this.posTableId || null,
                 product_id: this.productId || null,
                 notes: this.notes
             };

             try {
                 const res = await fetch('{{ route('public.storefront.reservation.submit', $business->slug) }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     },
                     body: JSON.stringify(payload)
                 });

                 const data = await res.json();
                 if (data.success) {
                     this.successData = data.reservation || { code: 'RESV-' + Math.floor(Math.random()*10000) };
                 } else {
                     this.errorMessage = data.message || 'Gagal mengirimkan reservasi. Silakan periksa kembali data Anda.';
                 }
             } catch (e) {
                 this.errorMessage = 'Terjadi kesalahan koneksi saat mengirim reservasi.';
             } finally {
                 this.isSubmitting = false;
             }
         }
     }">

    {{-- Breadcrumb & Title --}}
    <div class="text-center max-w-2xl mx-auto space-y-3">
        <div class="inline-block px-3 py-1 rounded-[8px] text-xs font-semibold theme-badge">Booking & Reservasi</div>
        <h1 class="font-heading font-extrabold text-3xl sm:text-4xl text-neutral-900 dark:text-white">
            Reservasi Meja & Janji Temu
        </h1>
        <p class="text-sm text-neutral-500">
            Pesan tempat atau jadwalkan janji layanan Anda di {{ $business->name }} lebih awal untuk memastikan ketersediaan dan kenyamanan optimal.
        </p>
    </div>

    {{-- Error Alert --}}
    <div x-show="errorMessage" x-cloak class="p-4 rounded-[12px] bg-red-50 dark:bg-red-950 border border-red-200 text-red-700 dark:text-red-300 text-sm">
        <span x-text="errorMessage"></span>
    </div>

    {{-- Success Confirmation Screen --}}
    <div x-show="successData" x-cloak class="p-8 rounded-[20px] bg-white dark:bg-neutral-800 border border-black/5 dark:border-white/10 shadow-xl text-center space-y-6">
        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center">
            <i data-lucide="check" class="w-8 h-8"></i>
        </div>
        <div>
            <h2 class="font-heading font-bold text-2xl text-neutral-900 dark:text-white">Reservasi Berhasil Diajukan!</h2>
            <p class="text-sm text-neutral-500 mt-1">Kode Reservasi Anda:</p>
            <div class="text-2xl font-extrabold text-theme-primary mt-1" style="font-variant-numeric: tabular-nums;" x-text="successData?.code"></div>
        </div>
        <p class="text-sm text-neutral-600 dark:text-neutral-300 max-w-md mx-auto leading-relaxed">
            Permintaan reservasi Anda telah kami terima. Tim kami akan segera mengonfirmasi jadwal Anda melalui nomor WhatsApp yang telah Anda cantumkan.
        </p>
        <div class="pt-4 flex justify-center gap-3">
            <a href="{{ url('/' . $business->slug) }}" class="px-5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 text-sm font-medium min-h-[44px] inline-flex items-center">
                Kembali ke Beranda
            </a>
            @if ($hasWhatsapp)
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" class="px-5 py-2.5 rounded-[12px] bg-emerald-500 text-white text-sm font-semibold flex items-center gap-1.5 min-h-[44px]">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Konfirmasi via WhatsApp</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Reservation Booking Form --}}
    <div x-show="!successData" class="p-6 sm:p-10 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-lg space-y-6">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- Guest Count --}}
            <div>
                <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                    Jumlah Tamu / Orang <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" 
                           x-model="guestCount" 
                           min="1" 
                           max="50" 
                           class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm text-neutral-900 dark:text-white">
                </div>
            </div>

            {{-- Date Picker --}}
            <div>
                <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                    Tanggal Kedatangan <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       x-model="reservationDate" 
                       min="{{ date('Y-m-d') }}"
                       class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm text-neutral-900 dark:text-white">
            </div>

            {{-- Time Slot --}}
            <div>
                <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                    Waktu / Jam Kedatangan <span class="text-red-500">*</span>
                </label>
                <select x-model="timeSlot" class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm">
                    @foreach (['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'] as $slot)
                        <option value="{{ $slot }}">{{ $slot }} WIB</option>
                    @endforeach
                </select>
            </div>

            {{-- Table Selection (If Resto Tables Exist) --}}
            @if ($posTables->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Pilihan Meja (Opsional)
                    </label>
                    <select x-model="posTableId" class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm">
                        <option value="">-- Bebas / Ditentukan Petugas --</option>
                        @foreach ($posTables as $tbl)
                            <option value="{{ $tbl->id }}">{{ $tbl->name ?: 'Meja ' . $tbl->table_number }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Service Selection (If Services Exist) --}}
            @if ($services->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Layanan Spesifik (Opsional)
                    </label>
                    <select x-model="productId" class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach ($services as $srv)
                            <option value="{{ $srv->id }}">{{ $srv->name }} (Rp {{ number_format((float) $srv->selling_price, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Customer Name --}}
            <div>
                <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                    Nama Pemesan <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       x-model="customerName" 
                       placeholder="Nama lengkap pemesan"
                       required
                       class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm">
            </div>

            {{-- Customer Phone / WhatsApp --}}
            <div>
                <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                    Nomor WhatsApp <span class="text-red-500">*</span>
                </label>
                <input type="tel" 
                       x-model="customerPhone" 
                       placeholder="081234567890"
                       required
                       class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm">
            </div>

            {{-- Customer Email --}}
            <div>
                <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                    Alamat Email (Opsional)
                </label>
                <input type="email" 
                       x-model="customerEmail" 
                       placeholder="email@domain.com"
                       class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm">
            </div>

            {{-- Notes --}}
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                    Permintaan Khusus / Catatan
                </label>
                <textarea x-model="notes" 
                          rows="3" 
                          placeholder="Contoh: Meja dekat jendela, perayaan ulang tahun, kursi bayi..."
                          class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm"></textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-black/5 dark:border-white/10 flex justify-end">
            <button type="button" 
                    @click="submitReservation()" 
                    :disabled="isSubmitting"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-[12px] theme-btn-primary font-bold text-sm shadow-md flex items-center justify-center gap-2 active:scale-[0.97] disabled:opacity-50 min-h-[48px]">
                <span x-show="!isSubmitting">Kirim Permintaan Reservasi</span>
                <span x-show="isSubmitting" x-cloak>Mengirim Permintaan...</span>
                <i data-lucide="arrow-right" class="w-4 h-4" x-show="!isSubmitting"></i>
            </button>
        </div>

    </div>

</div>
@endsection
