@extends('layouts.admin', [
    'title' => 'Edit Rekening ' . $paymentAccount->bank_name . ' - Admin Console',
    'headerTitle' => 'Edit Rekening Pembayaran',
    'headerSubtitle' => 'Perbarui data nomor rekening, instruksi transfer, atau gambar QRIS untuk tenant Cooca',
])

@section('content')
    <div class="max-w-3xl space-y-6" x-data="{
        type: '{{ old('type', $paymentAccount->type) }}',
        color: '{{ old('color', $paymentAccount->color) }}',
        qrPreview: null,
        previewQr(event) {
            const file = event.target.files[0];
            if (file) {
                this.qrPreview = URL.createObjectURL(file);
            }
        }
    }">

        <!-- Back Navigation Bar -->
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.payment-accounts.index') }}"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[10px] text-[13px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/8 hover:bg-[#007AFF]/15 active:scale-[0.97] transition">
                <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="2"></i>
                <span>Kembali ke Daftar Rekening</span>
            </a>
        </div>

        <!-- Form Error Summary -->
        @if ($errors->any())
            <div class="rounded-[18px] p-4 bg-[#FF3B30]/10 border border-[#FF3B30]/20 flex items-start gap-3">
                <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/20 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="alert-circle" class="w-4 h-4" stroke-width="2"></i>
                </div>
                <div>
                    <h4 class="text-[14px] font-bold text-[#FF3B30] dark:text-[#FF453A]">Mohon Lengkapi Data Berikut:</h4>
                    <ul class="text-[12px] text-[#FF3B30]/90 dark:text-[#FF453A]/90 mt-1 list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Bento Form Container -->
        <form method="POST" action="{{ route('admin.payment-accounts.update', $paymentAccount) }}" enctype="multipart/form-data"
            class="rounded-[22px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-8 shadow-sm space-y-7">
            @csrf
            @method('PUT')

            <!-- Section 1: Basic Information -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                        <i data-lucide="landmark" class="w-4 h-4" stroke-width="2"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Informasi Bank & Rekening</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Data identitas bank atau saluran penerimaan dana</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Bank Code -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Kode Bank / Identifier <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <input type="text" name="bank_code" value="{{ old('bank_code', $paymentAccount->bank_code) }}" required
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] uppercase font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kode unik sistem (huruf kecil/slug tanpa spasi).</p>
                    </div>

                    <!-- Payment Type -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Tipe Pembayaran <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <select name="type" x-model="type" required
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                            <option value="bank_transfer" {{ old('type', $paymentAccount->type) === 'bank_transfer' ? 'selected' : '' }}>
                                Transfer Bank Manual
                            </option>
                            <option value="qris" {{ old('type', $paymentAccount->type) === 'qris' ? 'selected' : '' }}>
                                QRIS (Scan Barcode / Semua Bank & e-Wallet)
                            </option>
                            <option value="e_wallet" {{ old('type', $paymentAccount->type) === 'e_wallet' ? 'selected' : '' }}>
                                e-Wallet (GoPay, OVO, ShopeePay, DANA)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Bank Name -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Nama Bank / Label Saluran <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $paymentAccount->bank_name) }}" required
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                    </div>

                    <!-- Account Name -->
                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Atas Nama Rekening (A/N) <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <input type="text" name="account_name" value="{{ old('account_name', $paymentAccount->account_name) }}" required
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                    </div>
                </div>

                <!-- Account Number -->
                <div>
                    <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                        Nomor Rekening / ID Merchant <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                    </label>
                    <input type="text" name="account_number" value="{{ old('account_number', $paymentAccount->account_number) }}" required
                        :placeholder="type === 'qris' ? 'Contoh: NMID: ID1020304050 atau Scan QR Code' : type === 'e_wallet' ? 'Contoh: 0812-XXXX-XXXX' : 'Contoh: 8735-0812-999'"
                        class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] font-mono tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                </div>

                <!-- Transfer Instructions -->
                <div>
                    <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                        Petunjuk / Panduan Transfer
                    </label>
                    <textarea name="instructions" rows="3"
                        class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">{{ old('instructions', $paymentAccount->instructions) }}</textarea>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Petunjuk cara transfer yang tampil kepada pengguna pada halaman instruksi bayar.</p>
                </div>
            </div>

            <!-- Section 2: QRIS & Visual Styling -->
            <div class="space-y-4 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                <div class="flex items-center gap-2.5 border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#30B0C7]/10 text-[#30B0C7] dark:text-[#40C8E0] flex items-center justify-center shrink-0">
                        <i data-lucide="qr-code" class="w-4 h-4" stroke-width="2"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Upload QR Code & Tampilan Visual</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Gambar barcode QRIS dan ikon identitas saluran</p>
                    </div>
                </div>

                <!-- Existing QRIS Display -->
                @if ($paymentAccount->qr_image_path)
                    <div class="flex items-center gap-4 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.08]">
                        <div class="w-20 h-20 rounded-[12px] bg-white p-1.5 border border-black/[0.08] flex items-center justify-center shrink-0 shadow-sm">
                            <img src="{{ $paymentAccount->qr_image_url }}" alt="QRIS saat ini"
                                class="max-h-full max-w-full object-contain rounded-[8px]">
                        </div>
                        <div>
                            <div class="text-[14px] font-bold text-black dark:text-white">QRIS Terpasang Saat Ini</div>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Unggah gambar baru di bawah jika ingin mengganti kode QRIS yang sudah ada.</p>
                            <a href="{{ $paymentAccount->qr_image_url }}" target="_blank"
                                class="text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1 mt-1.5">
                                <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
                                <span>Buka Ukuran Penuh</span>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- QR Image File Input -->
                <div>
                    <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                        {{ $paymentAccount->qr_image_path ? 'Ganti File Gambar QRIS (Opsional)' : 'Unggah File Gambar QRIS (Opsional)' }}
                    </label>
                    <input type="file" name="qr_image" accept="image/*" @change="previewQr($event)"
                        class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] px-3.5 py-2.5 text-[13px] text-black/70 dark:text-white/70 file:mr-4 file:py-1.5 file:px-3.5 file:rounded-[8px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] cursor-pointer transition">
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kosongkan jika tidak ingin mengubah barcode QRIS yang sudah aktif.</p>
                </div>

                <!-- QR Live Preview -->
                <div x-show="qrPreview" x-cloak
                    class="p-4 bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] rounded-[18px] max-w-xs text-center space-y-2">
                    <span class="text-[12px] font-semibold text-black/60 dark:text-white/60 block">Preview Barcode Baru:</span>
                    <div class="p-3 bg-white rounded-[14px] border border-black/5 shadow-inner">
                        <img :src="qrPreview" alt="QRIS Preview" class="max-h-48 mx-auto rounded-[10px] object-contain">
                    </div>
                </div>

                <!-- Icon, Color, and Sort Order -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Ikon Kartu
                        </label>
                        <select name="icon"
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                            <option value="credit-card" {{ old('icon', $paymentAccount->icon) === 'credit-card' ? 'selected' : '' }}>credit-card (Transfer)</option>
                            <option value="qr-code" {{ old('icon', $paymentAccount->icon) === 'qr-code' ? 'selected' : '' }}>qr-code (QRIS)</option>
                            <option value="wallet" {{ old('icon', $paymentAccount->icon) === 'wallet' ? 'selected' : '' }}>wallet (Dompet)</option>
                            <option value="landmark" {{ old('icon', $paymentAccount->icon) === 'landmark' ? 'selected' : '' }}>landmark (Bank)</option>
                            <option value="building-2" {{ old('icon', $paymentAccount->icon) === 'building-2' ? 'selected' : '' }}>building-2 (Perusahaan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Warna Aksen
                        </label>
                        <select name="color" x-model="color"
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                            <option value="blue">Biru (BCA / Mandiri)</option>
                            <option value="amber">Amber / Emas (Mandiri)</option>
                            <option value="cyan">Cyan (BRI)</option>
                            <option value="emerald">Emerald (QRIS)</option>
                            <option value="purple">Ungu (e-Wallet)</option>
                            <option value="indigo">Indigo (Umum)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Urutan Tampil
                        </label>
                        <input type="number" name="sort_order"
                            value="{{ old('sort_order', $paymentAccount->sort_order) }}" min="0" max="999"
                            class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] font-mono tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                    </div>
                </div>

                <!-- Active Toggle Switch -->
                <div class="pt-2">
                    <label class="flex items-center gap-3.5 cursor-pointer p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.05] dark:border-white/[0.08] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $paymentAccount->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                            class="w-5 h-5 rounded-[6px] border-black/20 text-[#34C759] focus:ring-[#34C759]/30">
                        <div>
                            <div class="font-bold text-[14px] text-black dark:text-white">Aktifkan Metode Pembayaran Ini</div>
                            <div class="text-[12px] text-black/50 dark:text-white/50">Tampilkan rekening ini sebagai opsi checkout bagi seluruh tenant Cooca.</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                <a href="{{ route('admin.payment-accounts.index') }}"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition inline-flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="2"></i>
                    <span>Batal</span>
                </a>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition flex items-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)]">
                    <i data-lucide="save" class="w-4 h-4" stroke-width="2"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>

        <!-- Reassurance & Danger Zone -->
        <div class="rounded-[20px] p-5 border border-[#FF3B30]/20 bg-[#FF3B30]/6 dark:bg-[#FF3B30]/10 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="alert-triangle" class="w-4 h-4" stroke-width="2"></i>
                </div>
                <div>
                    <div class="text-[14px] font-bold text-[#C41E17] dark:text-[#FF453A]">Zona Berbahaya: Hapus Rekening</div>
                    <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                        Menghapus rekening ini akan menghilangkannya secara permanen dari sistem. Riwayat transaksi sebelumnya tetap aman di database.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.payment-accounts.destroy', $paymentAccount) }}"
                onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin menghapus rekening {{ addslashes($paymentAccount->bank_name) }}? Tindakan ini tidak dapat dibatalkan.', 'Hapus Rekening Pembayaran?', 'danger')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition flex items-center justify-center gap-2 shrink-0 shadow-sm">
                    <i data-lucide="trash-2" class="w-4 h-4" stroke-width="2"></i>
                    <span>Hapus Rekening Ini</span>
                </button>
            </form>
        </div>

    </div>
@endsection
