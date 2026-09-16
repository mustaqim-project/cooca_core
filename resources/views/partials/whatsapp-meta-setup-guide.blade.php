{{-- PANDUAN LENGKAP LANGKAH-DEMI-LANGKAH (STEP-BY-STEP GUIDE) SETUP META WHATSAPP CLOUD API --}}
@php
    $guideMode = $mode ?? (auth('admin')->check() ? 'admin' : 'owner');
    $isAdminGuide = $guideMode === 'admin';
@endphp

<div x-data="{
    showGuide: false,
    currentStep: 1,
    totalSteps: 6,
    copiedText: null,
    copyToClipboard(text, id) {
        navigator.clipboard.writeText(text);
        this.copiedText = id;
        setTimeout(() => this.copiedText = null, 2000);
    }
}" class="rounded-[22px] bg-gradient-to-br from-[#007AFF]/8 via-white dark:via-[#1C1C1E] to-[#5856D6]/8 border border-[#007AFF]/25 shadow-sm overflow-hidden transition-all">

    {{-- 1. ACCORDION HEADER TRIGGER --}}
    <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer select-none"
        @click="showGuide = !showGuide">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-[14px] bg-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-md shadow-[#007AFF]/25">
                <i data-lucide="book-open" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">
                    @if($isAdminGuide)
                        Panduan Setup Meta WhatsApp Cloud API (Kanal Platform &amp; OTP)
                    @else
                        Panduan Setup Meta WhatsApp Cloud API (Nomor Resmi Toko Anda)
                    @endif
                </h3>
                <p class="text-[12.5px] text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                    @if($isAdminGuide)
                        Petunjuk ringkas untuk menghubungkan WhatsApp resmi pengelola Cooca guna mengirimkan kode OTP masuk dan pesan siaran/pengumuman.
                    @else
                        Petunjuk ringkas untuk menghubungkan WhatsApp resmi toko Anda guna mengirimkan struk belanja kasir POS dan notifikasi pesanan otomatis.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
            <span class="text-[12px] font-bold text-[#007AFF]" x-text="showGuide ? 'Tutup Panduan' : 'Buka Panduan (6 Langkah)'"></span>
            <div class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] flex items-center justify-center transition-transform duration-300"
                :class="showGuide ? 'rotate-180 bg-[#007AFF]/15 text-[#007AFF]' : 'text-black/60 dark:text-white/60'">
                <i data-lucide="chevron-down" class="w-4 h-4"></i>
            </div>
        </div>
    </div>

    {{-- 2. ACCORDION BODY (EXPANDABLE STEPPER) --}}
    <div x-show="showGuide" x-collapse x-cloak class="border-t border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-7 space-y-6">

        {{-- STEPPER NAVIGATION BAR (APPLE PILL STYLE) --}}
        <div class="p-1.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[16px] flex items-center gap-1.5 overflow-x-auto shadow-inner">
            <template x-for="step in [
                { num: 1, label: '1. Buat Aplikasi' },
                { num: 2, label: '2. Produk WhatsApp' },
                { num: 3, label: '3. Salin ID' },
                { num: 4, label: '4. Token Permanen' },
                { num: 5, label: '{{ $isAdminGuide ? '5. Nomor Platform' : '5. Nomor Toko' }}' },
                { num: 6, label: '{{ $isAdminGuide ? '6. Template OTP' : '6. Template Struk' }}' }
            ]" :key="step.num">
                <button type="button" @click="currentStep = step.num"
                    :class="currentStep === step.num ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[40px] px-3.5 rounded-[11px] text-[12px] transition-all flex items-center gap-2 shrink-0 whitespace-nowrap">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                        :class="currentStep === step.num ? 'bg-[#007AFF] text-white' : 'bg-black/10 dark:bg-white/10'"
                        x-text="step.num"></span>
                    <span x-text="step.label"></span>
                </button>
            </template>
        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 1: BUAT AKUN DEVELOPER & APLIKASI                                    --}}
        {{-- ========================================================================= --}}
        <div x-show="currentStep === 1" x-transition class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.05] dark:border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[9px] bg-[#007AFF] text-white flex items-center justify-center text-[12px] font-bold">1</span>
                    <h4 class="text-[15px] font-bold text-black dark:text-white">Langkah 1: Masuk ke Portal Meta for Developers &amp; Buat Aplikasi</h4>
                </div>
                <a href="https://developers.facebook.com" target="_blank" rel="noopener noreferrer"
                    class="px-3.5 py-1.5 rounded-[10px] text-[12px] font-bold bg-[#007AFF] hover:bg-[#0071E3] text-white transition-all inline-flex items-center gap-1.5 shadow-sm">
                    <span>Buka developers.facebook.com</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-[13px]">
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                    <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                        <span class="text-[#007AFF]">1.1</span> Login Akun Facebook
                    </div>
                    <p class="text-black/65 dark:text-white/65 leading-relaxed">
                        Buka <a href="https://developers.facebook.com" target="_blank" class="text-[#007AFF] underline font-semibold">developers.facebook.com</a> dan masuk memakai akun Facebook aktif Anda. Jika baru pertama kali, klik <em>Get Started</em> untuk mendaftar.
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                    <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                        <span class="text-[#007AFF]">1.2</span> Klik "Create App"
                    </div>
                    <p class="text-black/65 dark:text-white/65 leading-relaxed">
                        Ketuk tombol hijau <strong>Create App (Buat Aplikasi)</strong> di pojok kanan atas layar developer Anda.
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                    <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                        <span class="text-[#007AFF]">1.3</span> Pilih "Business" &amp; Nama
                    </div>
                    <p class="text-black/65 dark:text-white/65 leading-relaxed">
                        @if($isAdminGuide)
                            Pilih use case <strong>Other</strong> &gt; tipe <strong>Business</strong>. Beri nama aplikasi (contoh: <code>Cooca Gateway</code>) dan pilih Akun Meta Business Anda.
                        @else
                            Pilih use case <strong>Other</strong> &gt; tipe <strong>Business</strong>. Beri nama aplikasi sesuai nama usaha Anda (contoh: <code>Kopi Sejahtera WA</code>) dan pilih Akun Bisnis Anda.
                        @endif
                    </p>
                </div>
            </div>

            <div class="p-3.5 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                <span><strong>Tips Hemat:</strong> Tidak perlu kartu kredit. Meta memberikan <strong>1.000 kuota percakapan gratis setiap bulan</strong>.</span>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 2: SETUP PRODUK WHATSAPP                                             --}}
        {{-- ========================================================================= --}}
        <div x-show="currentStep === 2" x-transition class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.05] dark:border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[9px] bg-[#007AFF] text-white flex items-center justify-center text-[12px] font-bold">2</span>
                    <h4 class="text-[15px] font-bold text-black dark:text-white">Langkah 2: Tambahkan Produk WhatsApp ke Aplikasi</h4>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[13px]">
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                    <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                        <span class="text-[#007AFF]">2.1</span> Cari Produk "WhatsApp"
                    </div>
                    <p class="text-black/65 dark:text-white/65 leading-relaxed">
                        Di dashboard aplikasi Meta, gulir layar ke bawah dan temukan kotak bertuliskan <strong>WhatsApp</strong>.
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                    <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                        <span class="text-[#007AFF]">2.2</span> Klik "Set Up" (Siapkan)
                    </div>
                    <p class="text-black/65 dark:text-white/65 leading-relaxed">
                        Klik tombol <strong>Set Up</strong> pada kotak WhatsApp. Anda akan otomatis diarahkan ke menu <strong>API Setup</strong>.
                    </p>
                </div>
            </div>

            <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] text-[12px] text-black/70 dark:text-white/70 flex items-start gap-2.5">
                <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                <div class="space-y-0.5">
                    <span class="font-bold text-black dark:text-white">Info Praktis:</span>
                    <p>Meta otomatis menyiapkan nomor uji coba resmi (sandbox) untuk mencoba mengirim pesan pertama kali.</p>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 3: SALIN PHONE NUMBER ID & WABA ID                                   --}}
        {{-- ========================================================================= --}}
        <div x-show="currentStep === 3" x-transition class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.05] dark:border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[9px] bg-[#007AFF] text-white flex items-center justify-center text-[12px] font-bold">3</span>
                    <h4 class="text-[15px] font-bold text-black dark:text-white">Langkah 3: Salin Phone Number ID &amp; WABA ID</h4>
                </div>
                <a href="https://developers.facebook.com/apps/" target="_blank" class="text-[12px] font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                    <span>Menu API Setup</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <p class="text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
                Di menu sebelah kiri, klik <strong>WhatsApp &gt; API Setup</strong>. Pada kotak <strong>Step 1: Select phone numbers</strong>, salin kedua kode ID ini:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4.5 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">1. Phone Number ID</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#007AFF]/12 text-[#007AFF]">Wajib</span>
                    </div>
                    <div class="font-mono text-[14px] font-bold text-black dark:text-white select-all bg-black/[0.04] dark:bg-white/[0.06] p-2 rounded-[8px]">
                        Contoh: 104523984712398
                    </div>
                    <p class="text-[11.5px] text-black/60 dark:text-white/60">
                        Salin deretan 15 digit angka ini dan tempelkan ke kolom <strong>Phone Number ID</strong> di formulir Cooca.
                    </p>
                </div>

                <div class="p-4.5 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">2. WhatsApp Business Account ID</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#AF52DE]/12 text-[#AF52DE]">WABA ID</span>
                    </div>
                    <div class="font-mono text-[14px] font-bold text-black dark:text-white select-all bg-black/[0.04] dark:bg-white/[0.06] p-2 rounded-[8px]">
                        Contoh: 109283746501928
                    </div>
                    <p class="text-[11.5px] text-black/60 dark:text-white/60">
                        Salin deretan 15 digit angka ini dan tempelkan ke kolom <strong>WhatsApp Business Account ID (WABA ID)</strong> di Cooca.
                    </p>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 4: MEMBUAT PERMANENT ACCESS TOKEN (SYSTEM USER TOKEN)                --}}
        {{-- ========================================================================= --}}
        <div x-show="currentStep === 4" x-transition class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.05] dark:border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[9px] bg-[#FF9500] text-white flex items-center justify-center text-[12px] font-bold">4</span>
                    <h4 class="text-[15px] font-bold text-black dark:text-white">Langkah 4: Buat Token Permanen (Tidak Pernah Kedaluwarsa)</h4>
                </div>
                <a href="https://business.facebook.com/settings/system-users" target="_blank" rel="noopener noreferrer"
                    class="px-3.5 py-1.5 rounded-[10px] text-[12px] font-bold bg-[#FF9500] hover:bg-[#E08500] text-white transition-all inline-flex items-center gap-1.5 shadow-sm">
                    <span>Buka System Users Setting</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            {{-- Warning: Temporary Token vs Permanent Token --}}
            <div class="p-4 rounded-[16px] bg-[#FF9500]/12 border border-[#FF9500]/25 text-[12.5px] text-black/80 dark:text-white/80 space-y-1.5">
                <div class="font-bold text-[#B25E00] dark:text-[#FF9F0A] flex items-center gap-1.5">
                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                    <span>PENTING: Jangan Pakai Token 24 Jam!</span>
                </div>
                <p class="leading-relaxed">
                    Token di halaman <em>API Setup</em> hanya berlaku 24 jam. Agar WhatsApp jalan otomatis selamanya tanpa terputus, buat <strong>Permanent Access Token</strong> dengan 4 langkah mudah ini:
                </p>
            </div>

            <div class="space-y-3 text-[13px]">
                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">A</span>
                    <div class="space-y-1 text-black/75 dark:text-white/75">
                        <div>Buka menu <strong>System Users</strong>: <a href="https://business.facebook.com/settings/system-users" target="_blank" class="text-[#007AFF] font-bold underline">business.facebook.com/settings/system-users</a>.</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">B</span>
                    <div class="space-y-1 text-black/75 dark:text-white/75">
                        <div>Klik <strong>Add (Tambah)</strong>: Beri nama pengguna sistem (contoh: <code>{{ $isAdminGuide ? 'cooca-bot' : 'kasir-bot' }}</code>), atur peran <strong>Admin</strong>, lalu klik <em>Create</em>.</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">C</span>
                    <div class="space-y-2 text-black/75 dark:text-white/75 w-full">
                        <div>Klik tombol <strong>Generate New Token</strong>:</div>
                        <ul class="list-disc list-inside space-y-1 text-[12px] opacity-90 pl-1">
                            <li>Pilih Aplikasi Anda yang dibuat di Langkah 1.</li>
                            <li>Token Expiration: Pilih <strong>Never (Tidak pernah kedaluwarsa)</strong>.</li>
                            <li>Centang 2 izin akses berikut:</li>
                        </ul>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                            <div class="flex items-center justify-between p-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 font-mono text-[12px]">
                                <code>whatsapp_business_messaging</code>
                                <button type="button" @click="copyToClipboard('whatsapp_business_messaging', 'p1')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline shrink-0 ml-2"
                                    x-text="copiedText === 'p1' ? 'Tersalin!' : 'Salin'"></button>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 font-mono text-[12px]">
                                <code>whatsapp_business_management</code>
                                <button type="button" @click="copyToClipboard('whatsapp_business_management', 'p2')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline shrink-0 ml-2"
                                    x-text="copiedText === 'p2' ? 'Tersalin!' : 'Salin'"></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">D</span>
                    <div class="space-y-1 text-black/75 dark:text-white/75">
                        <div>Klik <strong>Generate Token</strong>. Salin kode token panjang berawalan <code>EAAG...</code> lalu tempelkan ke kolom <strong>Permanent Access Token</strong> di Cooca.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 5: TAUTKAN NOMOR WHATSAPP RESMI                                      --}}
        {{-- ========================================================================= --}}
        <div x-show="currentStep === 5" x-transition class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.05] dark:border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[9px] bg-[#007AFF] text-white flex items-center justify-center text-[12px] font-bold">5</span>
                    <h4 class="text-[15px] font-bold text-black dark:text-white">
                        @if($isAdminGuide)
                            Langkah 5: Tautkan Nomor WhatsApp Resmi Platform Cooca
                        @else
                            Langkah 5: Tautkan Nomor WhatsApp Resmi Toko Anda Sendiri
                        @endif
                    </h4>
                </div>
            </div>

            <p class="text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
                @if($isAdminGuide)
                    Hubungkan nomor WhatsApp resmi pengelola Cooca agar pesan OTP dan siaran platform terkirim dengan identitas resmi:
                @else
                    Hubungkan nomor WhatsApp resmi toko Anda agar saat pembeli menerima struk kasir atau update order, nama resmi toko Anda yang muncul di layar pembeli:
                @endif
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-[13px]">
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                    <div class="font-bold text-black dark:text-white">1. Klik Add Phone Number</div>
                    <p class="text-black/65 dark:text-white/65 leading-relaxed">
                        Di menu <strong>WhatsApp &gt; API Setup</strong>, klik tombol <strong>Add phone number (Tambah nomor)</strong>.
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                    <div class="font-bold text-black dark:text-white">2. Isi Nama Tampilan</div>
                    <p class="text-black/65 dark:text-white/65 leading-relaxed">
                        @if($isAdminGuide)
                            Masukkan Nama Resmi Platform (contoh: <code>Cooca Official</code>) dan pilih kategori layanan bisnis.
                        @else
                            Masukkan Nama Resmi Toko Anda (contoh: <code>Kopi Sejahtera POS</code>) dan pilih kategori bisnis toko Anda.
                        @endif
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                    <div class="font-bold text-black dark:text-white">3. Verifikasi SMS / Telpon</div>
                    <p class="text-black/65 dark:text-white/65 leading-relaxed">
                        Masukkan kode verifikasi 6 digit yang dikirimkan Meta via SMS atau Panggilan. Setelah aktif, salin <strong>Phone Number ID</strong> yang baru.
                    </p>
                </div>
            </div>

            <div class="p-3.5 rounded-[14px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[12px] text-black/75 dark:text-white/75 flex items-start gap-2.5">
                <i data-lucide="info" class="w-4 h-4 text-[#FF9500] shrink-0 mt-0.5"></i>
                <div>
                    <strong>Catatan Penting Nomor:</strong> Nomor yang didaftarkan ke Meta Cloud API tidak boleh sedang aktif di aplikasi WhatsApp HP biasa. Gunakan nomor baru, atau hapus akun WhatsApp di HP sebelum didaftarkan ke Meta.
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 6: TEMPLATE PESAN META                                               --}}
        {{-- ========================================================================= --}}
        <div x-show="currentStep === 6" x-transition class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.05] dark:border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[9px] bg-[#34C759] text-white flex items-center justify-center text-[12px] font-bold">6</span>
                    <h4 class="text-[15px] font-bold text-black dark:text-white">
                        @if($isAdminGuide)
                            Langkah 6: Daftarkan Template OTP Keamanan (cooca_otp) &amp; Selesai
                        @else
                            Langkah 6: Daftarkan Template Struk Kasir &amp; Notifikasi Pelanggan
                        @endif
                    </h4>
                </div>
            </div>

            @if($isAdminGuide)
                {{-- KONTEN LANGKAH 6 KHUSUS ADMIN (TEMPLATE OTP & SISTEM) --}}
                <div class="space-y-3 text-[13px]">
                    <p class="text-black/70 dark:text-white/70 leading-relaxed">
                        Agar kode OTP verifikasi akun dan login terkirim instan tanpa jeda, Meta mewajibkan penggunaan template resmi kategori <strong>AUTHENTICATION</strong>:
                    </p>

                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-black dark:text-white">Formulir Template di WhatsApp Manager:</span>
                            <a href="https://business.facebook.com/wa/manage/message-templates" target="_blank" class="text-[12px] font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                                <span>Buka Template Manager</span>
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                        <ul class="space-y-2 text-[12px] text-black/75 dark:text-white/75">
                            <li>• <strong>Category:</strong> Pilih <code>Authentication</code></li>
                            <li>• <strong>Template Name:</strong> Isikan <code class="font-bold text-[#007AFF]">cooca_otp</code></li>
                            <li>• <strong>Language:</strong> Pilih <code>Indonesian (id)</code> atau <code>English (US)</code></li>
                            <li>• <strong>Button Type:</strong> Pilih <code>Copy Code (Salin Kode)</code></li>
                        </ul>
                        <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] text-[11.5px] text-black/60 dark:text-white/60 flex items-start gap-2">
                            <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500] shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold text-black dark:text-white">Persetujuan Cepat:</span> Template kategori Authentication disetujui otomatis oleh AI Meta dalam waktu 1 hingga 5 menit.
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- KONTEN LANGKAH 6 KHUSUS BISNIS OWNER (STRUK KASIR & ORDER UPDATE) --}}
                <div class="space-y-3 text-[13px]">
                    <p class="text-black/70 dark:text-white/70 leading-relaxed">
                        Untuk mengirimkan struk digital kasir POS dan update pesanan ke pelanggan, gunakan template resmi kategori <strong>UTILITY</strong>:
                    </p>

                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-black dark:text-white">Formulir Template di WhatsApp Manager:</span>
                            <a href="https://business.facebook.com/wa/manage/message-templates" target="_blank" class="text-[12px] font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                                <span>Buka Template Manager</span>
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                        <ul class="space-y-2 text-[12px] text-black/75 dark:text-white/75">
                            <li>• <strong>Category:</strong> Pilih <code>Utility</code> (Layanan &amp; Transaksi)</li>
                            <li>• <strong>Template Name:</strong> Isikan <code class="font-bold text-[#007AFF]">struk_pembelian</code> (atau nama template struk toko Anda)</li>
                            <li>• <strong>Language:</strong> Pilih <code>Indonesian (id)</code></li>
                            <li>• <strong>Contoh Isi Pesan:</strong> <code>Halo @{{1}}, terima kasih telah berbelanja di @{{2}}. Berikut struk transaksi Anda: @{{3}}</code></li>
                        </ul>
                        <div class="p-3.5 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 shrink-0"></i>
                            <span><strong>Kabar Baik:</strong> Anda <strong>tidak perlu membuat template OTP</strong> karena verifikasi masuk dan login akun sudah ditangani otomatis oleh platform Cooca!</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="p-4 rounded-[16px] bg-[#34C759]/12 border border-[#34C759]/25 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#34C759] text-white flex items-center justify-center shrink-0">
                        <i data-lucide="check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-[14px] font-bold text-[#248A3D] dark:text-[#30D158]">Semua Langkah Selesai!</div>
                        <div class="text-[12px] text-black/65 dark:text-white/65">
                            @if($isAdminGuide)
                                Silakan tempelkan Token Permanen, Phone Number ID, dan WABA ID pada formulir di bawah ini, lalu pilih driver Meta Cloud API untuk jalur OTP atau Siaran.
                            @else
                                Silakan tempelkan Token Permanen, Phone Number ID, dan WABA ID pada formulir di bawah ini, lalu klik Simpan Kredensial. Nomor resmi toko Anda langsung aktif!
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEPPER BOTTOM CONTROLLER --}}
        <div class="flex items-center justify-between pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
            <button type="button" @click="currentStep = Math.max(1, currentStep - 1)"
                :disabled="currentStep === 1"
                class="min-h-[40px] px-4 rounded-[11px] text-[12px] font-bold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] disabled:opacity-40 disabled:pointer-events-none transition-all inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Sebelumnya</span>
            </button>

            <span class="text-[12px] font-mono text-black/50 dark:text-white/50" x-text="'Langkah ' + currentStep + ' dari ' + totalSteps"></span>

            <button type="button" @click="currentStep = Math.min(totalSteps, currentStep + 1)"
                :disabled="currentStep === totalSteps"
                class="min-h-[40px] px-4 rounded-[11px] text-[12px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-40 disabled:pointer-events-none transition-all inline-flex items-center gap-1.5 shadow-sm">
                <span>Selanjutnya</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </button>
        </div>

    </div>
</div>

