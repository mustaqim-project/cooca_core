{{-- PANDUAN RINGKAS METODE 1-KLIK META WHATSAPP CLOUD API (APPLE HIG BENTO DESIGN) --}}
@php
    $guideMode = $mode ?? (auth('admin')->check() ? 'admin' : 'owner');
    $isAdminGuide = $guideMode === 'admin';
@endphp

<div class="rounded-[18px] bg-[#1877F2]/6 border border-[#1877F2]/15 p-4 sm:p-5 space-y-3">
    <div class="flex items-center gap-2.5 text-[#1877F2] font-bold text-[13.5px]">
        <div class="w-7 h-7 rounded-[10px] bg-[#1877F2] text-white flex items-center justify-center shrink-0 shadow-sm shadow-[#1877F2]/20">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
        </div>
        <span>Metode 1-Klik Meta WhatsApp Resmi</span>
    </div>
    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
        Pendaftaran akun resmi dilakukan secara instan melalui popup resmi Meta Facebook. Cukup login ke akun Facebook Anda, pilih atau daftarkan nomor telepon WhatsApp bisnis, dan integrasi WhatsApp Cloud API langsung terhubung secara otomatis dengan kuota gratis 1.000 percakapan/bulan.
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-[11.5px]">
        <div class="p-2.5 rounded-[12px] bg-white/70 dark:bg-black/20 border border-black/5 dark:border-white/5">
            <span class="font-bold text-[#1877F2] block">1. Buka Popup Meta</span>
            <span class="text-black/60 dark:text-white/60">Klik tombol hubungkan</span>
        </div>
        <div class="p-2.5 rounded-[12px] bg-white/70 dark:bg-black/20 border border-black/5 dark:border-white/5">
            <span class="font-bold text-[#1877F2] block">2. Masuk Facebook</span>
            <span class="text-black/60 dark:text-white/60">Pilih akun &amp; nomor toko</span>
        </div>
        <div class="p-2.5 rounded-[12px] bg-white/70 dark:bg-black/20 border border-black/5 dark:border-white/5">
            <span class="font-bold text-[#34C759] block">3. Langsung Terhubung</span>
            <span class="text-black/60 dark:text-white/60">Siap kirim struk digital</span>
        </div>
    </div>
</div>
