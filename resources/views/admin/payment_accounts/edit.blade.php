@extends('layouts.admin', [
    'title' => 'Edit Rekening ' . $paymentAccount->bank_name . ' — Admin Console',
    'headerTitle' => 'Edit Rekening Pembayaran',
    'headerSubtitle' => 'Perbarui data nomor rekening, instruksi transfer, atau gambar QRIS'
])

@section('content')
<div class="max-w-3xl space-y-6" x-data="{
    type: '{{ old('type', $paymentAccount->type) }}',
    color: '{{ old('color', $paymentAccount->color) }}',
    qrPreview: null,
    previewQr(event) {
        const file = event.target.files[0];
        if (file) this.qrPreview = URL.createObjectURL(file);
    }
}">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.payment-accounts.index') }}" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.5"></i><span>Kembali ke Daftar Rekening</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.payment-accounts.update', $paymentAccount) }}" enctype="multipart/form-data" class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1: Basic Information -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                <i data-lucide="landmark" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Informasi Bank / Rekening</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Kode Bank / Identifier <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <input type="text" name="bank_code" value="{{ old('bank_code', $paymentAccount->bank_code) }}" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] uppercase text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kode unik sistem (huruf kecil tanpa spasi).</p>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Tipe Pembayaran <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <select name="type" x-model="type" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="bank_transfer" {{ old('type', $paymentAccount->type) === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank Manual</option>
                        <option value="qris" {{ old('type', $paymentAccount->type) === 'qris' ? 'selected' : '' }}>QRIS (Scan Barcode / e-Wallet)</option>
                        <option value="e_wallet" {{ old('type', $paymentAccount->type) === 'e_wallet' ? 'selected' : '' }}>e-Wallet (GoPay, OVO, ShopeePay, DANA)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Bank / Label Saluran <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $paymentAccount->bank_name) }}" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Atas Nama Rekening (A/N) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <input type="text" name="account_name" value="{{ old('account_name', $paymentAccount->account_name) }}" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nomor Rekening / ID Merchant <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                <input type="text" name="account_number" value="{{ old('account_number', $paymentAccount->account_number) }}" required :placeholder="type === 'qris' ? 'Contoh: NMID: ID1020304050' : type === 'e_wallet' ? 'Contoh: 0812-XXXX-XXXX' : 'Contoh: 8735-0812-999'" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Petunjuk / Panduan Transfer</label>
                <textarea name="instructions" rows="3" class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">{{ old('instructions', $paymentAccount->instructions) }}</textarea>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Petunjuk cara pembayaran yang tampil kepada pengguna pada halaman instruksi bayar.</p>
            </div>
        </div>

        <!-- Section 2: QRIS Image Upload -->
        <div class="space-y-4 pt-4 border-t border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                <i data-lucide="qr-code" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i>
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Upload Gambar QR Code QRIS</h3>
            </div>

            @if($paymentAccount->qr_image_path)
            <div class="flex items-center gap-4 p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05]">
                <img src="{{ $paymentAccount->qr_image_url }}" alt="QRIS saat ini" class="w-24 h-24 rounded-[10px] bg-white p-1.5 object-contain">
                <div>
                    <div class="text-[13px] font-semibold text-black dark:text-white">QRIS Terpasang Saat Ini</div>
                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Unggah gambar baru di bawah untuk menggantinya.</p>
                    <a href="{{ $paymentAccount->qr_image_url }}" target="_blank" class="text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1 mt-1"><i data-lucide="external-link" class="w-3 h-3" stroke-width="1.5"></i> Lihat ukuran penuh</a>
                </div>
            </div>
            @endif

            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Ganti File Gambar QRIS (PNG / JPG / WEBP)</label>
                <input type="file" name="qr_image" accept="image/*" @change="previewQr($event)" class="w-full bg-black/[0.04] dark:bg-white/[0.06] rounded-[10px] px-3 py-2 text-black/60 dark:text-white/60 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#34C759] file:text-white cursor-pointer">
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kosongkan jika tidak ingin mengubah gambar QRIS.</p>
            </div>

            <div x-show="qrPreview" x-cloak class="p-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 rounded-[12px] max-w-xs text-center space-y-2">
                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 block">Preview QR Code:</span>
                <img :src="qrPreview" alt="QRIS Preview" class="max-h-48 mx-auto rounded-[10px]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Icon Lucide</label>
                    <select name="icon" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="credit-card" {{ old('icon', $paymentAccount->icon) === 'credit-card' ? 'selected' : '' }}>credit-card</option>
                        <option value="qr-code" {{ old('icon', $paymentAccount->icon) === 'qr-code' ? 'selected' : '' }}>qr-code</option>
                        <option value="wallet" {{ old('icon', $paymentAccount->icon) === 'wallet' ? 'selected' : '' }}>wallet</option>
                        <option value="landmark" {{ old('icon', $paymentAccount->icon) === 'landmark' ? 'selected' : '' }}>landmark</option>
                        <option value="building-2" {{ old('icon', $paymentAccount->icon) === 'building-2' ? 'selected' : '' }}>building-2</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Warna Aksen</label>
                    <select name="color" x-model="color" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="blue">Biru (BCA / Mandiri)</option>
                        <option value="amber">Amber / Emas (Mandiri)</option>
                        <option value="cyan">Cyan (BRI)</option>
                        <option value="emerald">Emerald (QRIS)</option>
                        <option value="purple">Ungu (e-Wallet)</option>
                        <option value="indigo">Indigo (Umum)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nomor Urutan Tampil</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $paymentAccount->sort_order) }}" min="0" max="999" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer p-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $paymentAccount->is_active ? '1' : '0') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded-[4px] border-black/20 text-[#34C759] dark:text-[#30D158] focus:ring-[#34C759]/30">
                    <div>
                        <div class="font-semibold text-black dark:text-white">Aktifkan Metode Pembayaran Ini</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">Tampilkan rekening ini sebagai opsi checkout bagi tenant.</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="pt-4 flex items-center justify-end gap-3 border-t border-black/5 dark:border-white/10">
            <a href="{{ route('admin.payment-accounts.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5"><i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.5"></i>Batal</a>
            <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="save" class="w-4 h-4" stroke-width="1.5"></i>Simpan Perubahan</button>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="rounded-[14px] px-4 py-4 border border-[#FF3B30]/25 bg-[#FF3B30]/8">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div>
                <div class="text-[14px] font-semibold text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2"><i data-lucide="alert-triangle" class="w-4 h-4" stroke-width="1.5"></i>Zona Berbahaya</div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Hapus rekening ini secara permanen dari seluruh opsi pembayaran tenant.</p>
            </div>
            <form method="POST" action="{{ route('admin.payment-accounts.destroy', $paymentAccount) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin menghapus rekening {{ addslashes($paymentAccount->bank_name) }}?', 'Hapus Rekening?', 'danger')">
                @csrf
                @method('DELETE')
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-2"><i data-lucide="trash-2" class="w-4 h-4" stroke-width="1.5"></i>Hapus Rekening Ini</button>
            </form>
        </div>
    </div>
</div>
@endsection