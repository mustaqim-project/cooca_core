@extends('layouts.public_marketing', ['title' => 'Pemulihan Akses Akun & Reset Kontak - Cooca', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <!-- Apple HIG Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-[20px] bg-[#FF9500]/10 text-[#FF9500] dark:bg-[#FF9F0A]/15 dark:text-[#FF9F0A] mb-3.5 shadow-sm">
                    <i data-lucide="shield-alert" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Pemulihan Akses
                    Akun</h1>
                <p class="mt-2 text-sm text-black/60 dark:text-white/60 max-w-lg mx-auto">
                    Gunakan layanan ini jika Anda kehilangan akses ke nomor WhatsApp, HP hilang/rusak, atau alamat email
                    terdaftar tidak dapat dibuka.
                </p>
            </div>

            <!-- Main Card -->
            <div
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-9 shadow-2xl shadow-black/5 dark:shadow-black/50 transition-all">
                @if ($errors->any())
                    <div id="error-summary-box"
                        class="mb-6 p-4 rounded-[20px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-sm animate-shake">
                        <div class="flex items-center gap-2 font-bold mb-2">
                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                            <span>Terdapat {{ $errors->count() }} Kendala Pada Formulir:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs mb-3 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <div
                            class="p-3 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-xs flex items-start gap-2">
                            <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span><strong>Pemberitahuan Berkas:</strong> Demi keamanan browser, berkas KTP &amp; dokumen
                                usaha wajib dipilih kembali jika formulir sempat gagal divalidasi.</span>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const el = document.getElementById('error-summary-box');
                            if (el) el.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        });
                    </script>
                @endif

                <form method="POST" action="{{ route('account-recovery.store') }}" enctype="multipart/form-data"
                    class="space-y-6" x-data="{
                        issueType: '{{ old('issue_type', 'both') }}',
                        submitting: false,
                        ktpPreview: null,
                        bizPreview: null,
                        selfiePreview: null,
                        handleFilePreview(e, target) {
                            const file = e.target.files[0];
                            if (!file) return;
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = (event) => {
                                    this[target] = event.target.result;
                                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                                };
                                reader.readAsDataURL(file);
                            } else {
                                this[target] = 'pdf';
                                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                            }
                        }
                    }" @submit="submitting = true">
                    @csrf

                    <!-- 1. Pilihan Kendala Bento Grid -->
                    <div>
                        <label
                            class="block text-xs sm:text-sm font-bold text-black/80 dark:text-white/85 uppercase tracking-wider mb-3">
                            1. Jenis Kendala Verifikasi <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label
                                class="relative flex flex-col p-4 rounded-[18px] border cursor-pointer transition-all text-left"
                                :class="issueType === 'phone_lost' ?
                                    'bg-[#007AFF]/10 border-[#007AFF] ring-2 ring-[#007AFF]/30 text-black dark:text-white shadow-sm' :
                                    'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08] text-black/60 dark:text-white/60 hover:border-black/20 dark:hover:border-white/20'">
                                <input type="radio" name="issue_type" value="phone_lost" x-model="issueType"
                                    class="sr-only">
                                <div
                                    class="flex items-center gap-2 mb-1.5 font-bold text-xs sm:text-sm text-black dark:text-white">
                                    <i data-lucide="smartphone" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                                    <span>HP / WA Hilang</span>
                                </div>
                                <span class="text-xs leading-relaxed opacity-80">HP hilang/rusak atau nomor WhatsApp tidak
                                    aktif lagi.</span>
                            </label>

                            <label
                                class="relative flex flex-col p-4 rounded-[18px] border cursor-pointer transition-all text-left"
                                :class="issueType === 'email_inaccessible' ?
                                    'bg-[#007AFF]/10 border-[#007AFF] ring-2 ring-[#007AFF]/30 text-black dark:text-white shadow-sm' :
                                    'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08] text-black/60 dark:text-white/60 hover:border-black/20 dark:hover:border-white/20'">
                                <input type="radio" name="issue_type" value="email_inaccessible" x-model="issueType"
                                    class="sr-only">
                                <div
                                    class="flex items-center gap-2 mb-1.5 font-bold text-xs sm:text-sm text-black dark:text-white">
                                    <i data-lucide="mail-x" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                                    <span>Email Terkunci</span>
                                </div>
                                <span class="text-xs leading-relaxed opacity-80">Email lama tidak bisa dibuka, hangus, atau
                                    lupa akses.</span>
                            </label>

                            <label
                                class="relative flex flex-col p-4 rounded-[18px] border cursor-pointer transition-all text-left"
                                :class="issueType === 'both' ?
                                    'bg-[#007AFF]/10 border-[#007AFF] ring-2 ring-[#007AFF]/30 text-black dark:text-white shadow-sm' :
                                    'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08] text-black/60 dark:text-white/60 hover:border-black/20 dark:hover:border-white/20'">
                                <input type="radio" name="issue_type" value="both" x-model="issueType" class="sr-only">
                                <div
                                    class="flex items-center gap-2 mb-1.5 font-bold text-xs sm:text-sm text-black dark:text-white">
                                    <i data-lucide="shield-alert" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]"></i>
                                    <span>Keduanya Bermasalah</span>
                                </div>
                                <span class="text-xs leading-relaxed opacity-80">HP hilang dan email terdaftar juga tidak
                                    dapat dibuka.</span>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Data Akun Terdaftar Saat Ini -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <label
                            class="block text-xs sm:text-sm font-bold text-black/80 dark:text-white/85 uppercase tracking-wider mb-3">
                            2. Data Akun &amp; Bisnis Terdaftar
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="applicant_name"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    Nama Lengkap Pemilik <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="applicant_name" id="applicant_name" required
                                    value="{{ old('applicant_name', $prefill['applicant_name']) }}"
                                    placeholder="Nama sesuai KTP"
                                    class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('applicant_name') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                @error('applicant_name')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="business_name"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    Nama Bisnis / Usaha <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="business_name" id="business_name" required
                                    value="{{ old('business_name', $prefill['business_name']) }}"
                                    placeholder="Contoh: Kopi Senja Mandiri"
                                    class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('business_name') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                @error('business_name')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="old_email"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    Email Akun Terdaftar <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="email" name="old_email" id="old_email" required
                                    value="{{ old('old_email', $prefill['old_email']) }}"
                                    placeholder="email.terdaftar@gmail.com"
                                    class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('old_email') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                @error('old_email')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="old_phone"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    Nomor WhatsApp Lama (Opsional)
                                </label>
                                <input type="text" name="old_phone" id="old_phone"
                                    value="{{ old('old_phone', $prefill['old_phone']) }}"
                                    placeholder="081234567890 (jika ingat)"
                                    class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('old_phone') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                @error('old_phone')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 3. Kontak Baru Yang Aktif -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                            <label
                                class="block text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">
                                3. Kontak Baru Pengganti (Wajib Aktif)
                            </label>
                        </div>
                        <p class="text-xs text-black/55 dark:text-white/55 mb-3">Setelah disetujui Administrator, akun Anda
                            akan otomatis dialihkan ke kontak baru ini.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="new_email"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    Alamat Email Baru <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                        <i data-lucide="mail" class="w-4 h-4"></i>
                                    </div>
                                    <input type="email" name="new_email" id="new_email" required
                                        value="{{ old('new_email') }}" placeholder="emailbaru.aktif@gmail.com"
                                        class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('new_email') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                </div>
                                @error('new_email')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block font-medium">{{ $message }}</span>
                                @else
                                    <p class="mt-1.5 text-[11px] sm:text-xs text-black/50 dark:text-white/50">Pastikan email
                                        baru ini belum terdaftar di akun Cooca lain.</p>
                                @enderror
                            </div>

                            <div>
                                <label for="new_phone"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    Nomor WhatsApp Baru <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                        <i data-lucide="phone" class="w-4 h-4"></i>
                                    </div>
                                    <input type="text" name="new_phone" id="new_phone" required
                                        value="{{ old('new_phone') }}" placeholder="08123456789 atau 628123456789"
                                        class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('new_phone') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                </div>
                                @error('new_phone')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block font-medium">{{ $message }}</span>
                                @else
                                    <p class="mt-1.5 text-[11px] sm:text-xs text-black/50 dark:text-white/50">Nomor ini harus
                                        aktif di WhatsApp untuk menerima notifikasi persetujuan.</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 4. Kronologi & Alasan -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <label for="reason_description"
                            class="block text-xs sm:text-sm font-bold text-black/80 dark:text-white/85 uppercase tracking-wider mb-2">
                            4. Kronologi &amp; Penjelasan Kendala <span class="text-[#FF3B30]">*</span>
                        </label>
                        <textarea name="reason_description" id="reason_description" rows="3" required
                            placeholder="Jelaskan secara ringkas bagaimana Anda kehilangan akses HP / nomor WhatsApp atau mengapa email tidak dapat dibuka..."
                            class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('reason_description') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 text-[16px] sm:text-sm transition-all outline-none leading-relaxed">{{ old('reason_description') }}</textarea>
                        @error('reason_description')
                            <span
                                class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- 5. Upload Berkas Bukti Otentik -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="paperclip" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                            <label
                                class="block text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">
                                5. Berkas Bukti Otentik Kepemilikan Akun
                            </label>
                        </div>
                        <p class="text-xs text-black/55 dark:text-white/55 mb-3.5">Lampirkan dokumen pendukung asli berikut
                            (Maks. 5MB per file, format JPG/PNG/PDF):</p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <!-- Bukti KTP -->
                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border @error('identity_card') border-[#FF3B30] ring-2 ring-[#FF3B30]/30 @else border-black/[0.06] dark:border-white/[0.08] @enderror flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs sm:text-sm font-bold text-black dark:text-white">Foto KTP /
                                            SIM</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] uppercase">Wajib</span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mb-3">Identitas resmi pemilik
                                        usaha.</p>
                                </div>
                                <div>
                                    <input type="file" name="identity_card" id="identity_card" required
                                        accept="image/*,.pdf" @change="handleFilePreview($event, 'ktpPreview')"
                                        class="w-full max-w-full block text-xs text-black/60 dark:text-white/60 file:mr-2 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-xs file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] file:cursor-pointer">
                                    @error('identity_card')
                                        <span
                                            class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1.5 block font-medium">{{ $message }}</span>
                                    @enderror
                                    <template x-if="ktpPreview && ktpPreview !== 'pdf'">
                                        <div
                                            class="mt-2.5 relative rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 max-h-24 bg-black/5">
                                            <img :src="ktpPreview" class="w-full h-24 object-cover">
                                        </div>
                                    </template>
                                    <template x-if="ktpPreview === 'pdf'">
                                        <div
                                            class="mt-2.5 p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-xs text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                            <i data-lucide="file-text" class="w-4 h-4 text-[#FF3B30]"></i>
                                            <span>Dokumen PDF terpilih</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Bukti Usaha -->
                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border @error('business_proof') border-[#FF3B30] ring-2 ring-[#FF3B30]/30 @else border-black/[0.06] dark:border-white/[0.08] @enderror flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs sm:text-sm font-bold text-black dark:text-white">Bukti
                                            Usaha</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] uppercase">Wajib</span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mb-3">NIB / SIUP / SKU / Foto
                                        Toko.</p>
                                </div>
                                <div>
                                    <input type="file" name="business_proof" id="business_proof" required
                                        accept="image/*,.pdf" @change="handleFilePreview($event, 'bizPreview')"
                                        class="w-full max-w-full block text-xs text-black/60 dark:text-white/60 file:mr-2 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-xs file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] file:cursor-pointer">
                                    @error('business_proof')
                                        <span
                                            class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1.5 block font-medium">{{ $message }}</span>
                                    @enderror
                                    <template x-if="bizPreview && bizPreview !== 'pdf'">
                                        <div
                                            class="mt-2.5 relative rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 max-h-24 bg-black/5">
                                            <img :src="bizPreview" class="w-full h-24 object-cover">
                                        </div>
                                    </template>
                                    <template x-if="bizPreview === 'pdf'">
                                        <div
                                            class="mt-2.5 p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-xs text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                            <i data-lucide="file-text" class="w-4 h-4 text-[#FF3B30]"></i>
                                            <span>Dokumen PDF terpilih</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Selfie KTP (Opsional) -->
                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border @error('selfie_proof') border-[#FF3B30] ring-2 ring-[#FF3B30]/30 @else border-black/[0.06] dark:border-white/[0.08] @enderror flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs sm:text-sm font-bold text-black dark:text-white">Selfie Pegang
                                            KTP</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] uppercase">Dianjurkan</span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mb-3">Foto wajah memegang KTP
                                        fisik.</p>
                                </div>
                                <div>
                                    <input type="file" name="selfie_proof" id="selfie_proof" accept="image/*"
                                        @change="handleFilePreview($event, 'selfiePreview')"
                                        class="w-full max-w-full block text-xs text-black/60 dark:text-white/60 file:mr-2 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-xs file:font-semibold file:bg-black/[0.08] dark:file:bg-white/[0.1] file:text-black dark:file:text-white hover:file:bg-black/[0.12] file:cursor-pointer">
                                    @error('selfie_proof')
                                        <span
                                            class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1.5 block font-medium">{{ $message }}</span>
                                    @enderror
                                    <template x-if="selfiePreview">
                                        <div
                                            class="mt-2.5 relative rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 max-h-24 bg-black/5">
                                            <img :src="selfiePreview" class="w-full h-24 object-cover">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notice & Submit Button -->
                    <div class="pt-4">
                        <div
                            class="p-4 rounded-[18px] bg-[#007AFF]/10 border border-[#007AFF]/25 text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm flex items-start gap-3 mb-5">
                            <i data-lucide="info" class="w-5 h-5 shrink-0 mt-0.5"></i>
                            <div class="leading-relaxed">
                                Permohonan Anda akan diverifikasi langsung oleh <strong>Tim Auditor Cooca Platform</strong>
                                untuk melindungi data toko dari pembajakan. Nomor tiket pelacakan akan otomatis diterbitkan
                                setelah formulir dikirim.
                            </div>
                        </div>

                        <button type="submit" :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                            class="w-full min-h-[52px] py-4 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2.5 active:scale-[0.98]">
                            <i data-lucide="shield-check" class="w-5 h-5" x-show="!submitting"></i>
                            <i data-lucide="loader-2" class="w-5 h-5 animate-spin" x-show="submitting"
                                style="display: none;"></i>
                            <span
                                x-text="submitting ? 'Mengirim Berkas & Permohonan...' : 'Ajukan Permohonan Pemulihan Akses'"></span>
                        </button>
                    </div>
                </form>

                <!-- Footer Links -->
                <div
                    class="mt-8 pt-6 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                    <a href="{{ route('account-recovery.check') }}"
                        class="text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors flex items-center gap-1.5 font-semibold py-1">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Sudah Punya Tiket? Cek Status Permohonan</span>
                    </a>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('login') }}"
                            class="text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white transition-colors py-1">
                            Batal & Kembali ke Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
