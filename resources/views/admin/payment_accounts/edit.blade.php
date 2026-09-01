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
        if (file) {
            this.qrPreview = URL.createObjectURL(file);
        }
    }
}">

    <!-- Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.payment-accounts.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Rekening</span>
        </a>
    </div>

    <!-- Edit Form Card -->
    <div class="glass-card p-6 sm:p-8 rounded-3xl space-y-6 border border-slate-800">
        <form method="POST" action="{{ route('admin.payment-accounts.update', $paymentAccount) }}" enctype="multipart/form-data" class="space-y-6 text-xs">
            @csrf
            @method('PUT')

            <!-- Section 1: Basic Information -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i data-lucide="landmark" class="w-4 h-4 text-emerald-400"></i>
                    <h3 class="text-sm font-bold text-white">Informasi Bank / Rekening</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Bank Code -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Kode Bank / Identifier <span class="text-rose-400">*</span></label>
                        <input type="text" name="bank_code" value="{{ old('bank_code', $paymentAccount->bank_code) }}" required
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono text-xs uppercase">
                        <p class="text-[11px] text-slate-500 mt-1">Kode unik sistem (huruf kecil tanpa spasi).</p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Tipe Pembayaran <span class="text-rose-400">*</span></label>
                        <select name="type" x-model="type" required
                                class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs">
                            <option value="bank_transfer" {{ old('type', $paymentAccount->type) === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank Manual</option>
                            <option value="qris" {{ old('type', $paymentAccount->type) === 'qris' ? 'selected' : '' }}>QRIS (Scan Barcode / e-Wallet)</option>
                            <option value="e_wallet" {{ old('type', $paymentAccount->type) === 'e_wallet' ? 'selected' : '' }}>e-Wallet (GoPay, OVO, ShopeePay, DANA)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Bank Name -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Nama Bank / Label Saluran <span class="text-rose-400">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $paymentAccount->bank_name) }}" required
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs">
                    </div>

                    <!-- Account Name (A/N) -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Atas Nama Rekening (A/N) <span class="text-rose-400">*</span></label>
                        <input type="text" name="account_name" value="{{ old('account_name', $paymentAccount->account_name) }}" required
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs">
                    </div>
                </div>

                <!-- Account Number -->
                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Nomor Rekening / ID Merchant <span class="text-rose-400">*</span></label>
                    <input type="text" name="account_number" value="{{ old('account_number', $paymentAccount->account_number) }}" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono text-xs">
                </div>

                <!-- Instructions -->
                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Petunjuk / Panduan Transfer</label>
                    <textarea name="instructions" rows="3"
                              class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs leading-relaxed">{{ old('instructions', $paymentAccount->instructions) }}</textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Petunjuk cara pembayaran yang tampil kepada pengguna pada halaman instruksi bayar.</p>
                </div>
            </div>

            <!-- Section 2: QRIS Image Upload -->
            <div class="space-y-4 pt-4 border-t border-slate-800">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i data-lucide="qr-code" class="w-4 h-4 text-teal-400"></i>
                    <h3 class="text-sm font-bold text-white">Upload / Ganti Gambar QR Code QRIS</h3>
                </div>

                @if($paymentAccount->qr_image_path)
                <div class="p-4 bg-slate-950 border border-slate-800 rounded-2xl flex items-center gap-4">
                    <img src="{{ $paymentAccount->qr_image_url }}" alt="QRIS Sekarang" class="w-20 h-20 object-contain rounded-xl bg-white p-1 shrink-0">
                    <div class="space-y-1 text-xs">
                        <span class="font-bold text-white block">Gambar QRIS Saat Ini Aktif</span>
                        <p class="text-slate-400">Unggah file gambar baru di bawah jika ingin mengganti kode QRIS ini.</p>
                    </div>
                </div>
                @endif

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">File Gambar QRIS Baru (PNG / JPG / WEBP)</label>
                    <input type="file" name="qr_image" accept="image/*" @change="previewQr($event)"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-200 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-500 file:text-slate-950 hover:file:bg-emerald-400 cursor-pointer">
                    <p class="text-[11px] text-slate-500 mt-1">Kosongkan jika tidak ingin mengubah gambar QRIS saat ini.</p>
                </div>

                <!-- Live Preview of new QR Image -->
                <div x-show="qrPreview" x-cloak class="p-3 bg-slate-950 border border-slate-800 rounded-2xl max-w-xs text-center space-y-2">
                    <span class="text-[11px] font-bold text-slate-400 block">Preview QR Code Baru:</span>
                    <img :src="qrPreview" alt="QRIS Preview Baru" class="max-h-48 mx-auto object-contain rounded-xl bg-white p-2">
                </div>
            </div>

            <!-- Section 3: Visual Style & Sorting -->
            <div class="space-y-4 pt-4 border-t border-slate-800">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i data-lucide="palette" class="w-4 h-4 text-purple-400"></i>
                    <h3 class="text-sm font-bold text-white">Tampilan & Urutan</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Icon -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Icon Lucide</label>
                        <select name="icon" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs">
                            <option value="credit-card" {{ old('icon', $paymentAccount->icon) === 'credit-card' ? 'selected' : '' }}>credit-card</option>
                            <option value="qr-code" {{ old('icon', $paymentAccount->icon) === 'qr-code' ? 'selected' : '' }}>qr-code</option>
                            <option value="wallet" {{ old('icon', $paymentAccount->icon) === 'wallet' ? 'selected' : '' }}>wallet</option>
                            <option value="landmark" {{ old('icon', $paymentAccount->icon) === 'landmark' ? 'selected' : '' }}>landmark</option>
                            <option value="building-2" {{ old('icon', $paymentAccount->icon) === 'building-2' ? 'selected' : '' }}>building-2</option>
                        </select>
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Warna Aksen</label>
                        <select name="color" x-model="color" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white text-xs">
                            <option value="blue" {{ old('color', $paymentAccount->color) === 'blue' ? 'selected' : '' }}>Biru (BCA / Mandiri)</option>
                            <option value="amber" {{ old('color', $paymentAccount->color) === 'amber' ? 'selected' : '' }}>Amber / Emas (Mandiri)</option>
                            <option value="cyan" {{ old('color', $paymentAccount->color) === 'cyan' ? 'selected' : '' }}>Cyan (BRI)</option>
                            <option value="emerald" {{ old('color', $paymentAccount->color) === 'emerald' ? 'selected' : '' }}>Emerald (QRIS)</option>
                            <option value="purple" {{ old('color', $paymentAccount->color) === 'purple' ? 'selected' : '' }}>Ungu (e-Wallet)</option>
                            <option value="indigo" {{ old('color', $paymentAccount->color) === 'indigo' ? 'selected' : '' }}>Indigo (Umum)</option>
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1.5">Nomor Urutan Tampil</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $paymentAccount->sort_order) }}" min="0" max="999"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono text-xs">
                    </div>
                </div>

                <!-- Active Toggle -->
                <div class="pt-2">
                    <label class="flex items-center gap-3 cursor-pointer p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $paymentAccount->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                               class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-emerald-600 focus:ring-emerald-500/20">
                        <div>
                            <div class="font-bold text-white">Aktifkan Metode Pembayaran Ini</div>
                            <div class="text-[11px] text-slate-400">Tampilkan rekening ini sebagai opsi checkout bagi tenant.</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('admin.payment-accounts.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
