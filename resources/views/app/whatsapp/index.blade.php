@extends('layouts.app', [
    'title' => 'WhatsApp Gateway — ' . $business->name,
    'headerTitle' => 'WhatsApp Gateway & Otomasi',
    'headerSubtitle' => 'Hubungkan nomor WhatsApp bisnis Anda untuk kirim struk digital POS & blast promosi pelanggan'
])

@section('content')
<div class="space-y-6" x-data="waGateway()" x-init="init()">

    <!-- Module Navigation Sub-Tabs -->
    <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-100 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 w-full sm:w-auto overflow-x-auto text-xs font-bold">
        <a href="{{ route('whatsapp.index') }}"
            class="px-4 py-2 rounded-xl bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-xs border border-slate-200/60 dark:border-slate-800 flex items-center gap-2 whitespace-nowrap">
            <i data-lucide="smartphone" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            <span>Koneksi Gateway</span>
        </a>
        <a href="{{ route('whatsapp.broadcast.index') }}"
            class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
            <i data-lucide="megaphone" class="w-4 h-4"></i>
            <span>Blast Promosi</span>
        </a>
        <a href="{{ route('whatsapp.logs.index') }}"
            class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
            <i data-lucide="history" class="w-4 h-4"></i>
            <span>Log Pesan</span>
        </a>
        <div class="border-l border-slate-200 dark:border-slate-800 h-5 my-auto mx-1 hidden sm:block"></div>
        <a href="{{ route('pos.terminal') }}"
            class="px-3.5 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 flex items-center gap-1.5 whitespace-nowrap transition-colors">
            <i data-lucide="calculator" class="w-4 h-4"></i>
            <span>Terminal Kasir POS</span>
        </a>
    </div>

    <!-- Main Gateway Cockpit Grid (2 Columns on Desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- ========================================== -->
        <!-- LEFT COLUMN: GATEWAY CONNECTION & QR SCAN  -->
        <!-- ========================================== -->
        <div class="lg:col-span-7 space-y-6">

            <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-6 space-y-5 transition-colors">
                
                <!-- Card Header with Real-Time Status Badge -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Status Koneksi WhatsApp</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Sinkronisasi nomor HP bisnis ke sistem cloud Cooca</p>
                        </div>
                    </div>

                    <div>
                        <!-- Connected Badge -->
                        <span x-show="status === 'connected'"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Terhubung</span>
                        </span>
                        <!-- Scan QR Badge -->
                        <span x-show="status === 'scan_qr'"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Menunggu Scan</span>
                        </span>
                        <!-- Disconnected Badge -->
                        <span x-show="status === 'disconnected' || status === ''"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                            <span>Tidak Terhubung</span>
                        </span>
                    </div>
                </div>

                <!-- 1. CONNECTED STATE -->
                <div x-show="status === 'connected'" x-transition.opacity class="space-y-4">
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-50/70 to-teal-50/40 dark:from-emerald-950/20 dark:to-teal-950/10 border border-emerald-200/80 dark:border-emerald-500/30 flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center shrink-0 text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="check-circle" class="w-7 h-7"></i>
                        </div>
                        <div class="space-y-0.5">
                            <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nomor WhatsApp Aktif</div>
                            <div class="text-xl font-extrabold text-slate-900 dark:text-white font-mono" x-text="phone ? '+' + phone : 'Sesi Aktif Terhubung'"></div>
                            <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-1.5 pt-0.5">
                                <i data-lucide="smartphone" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span x-text="deviceName ? 'Perangkat: ' + deviceName : 'WhatsApp Web Multi-Device'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <a href="{{ route('whatsapp.broadcast.create') }}"
                            class="flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all active:scale-98">
                            <i data-lucide="megaphone" class="w-4 h-4"></i>
                            <span>Buat Blast Promosi</span>
                        </a>
                        <button @click="disconnectWa()"
                            class="flex items-center justify-center gap-2 py-3 rounded-xl bg-white hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold text-xs transition-all border border-slate-200 dark:border-slate-700 hover:border-rose-300 dark:hover:border-rose-500/40 active:scale-98"
                            :disabled="isLoading">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Putus Koneksi Sesi</span>
                        </button>
                    </div>
                </div>

                <!-- 2. SCAN QR CODE STATE -->
                <div x-show="status === 'scan_qr'" x-transition.opacity class="text-center space-y-5 py-2">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pindai Kode QR dengan WhatsApp</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Arahkan kamera WhatsApp HP Anda ke kode QR di bawah</p>
                    </div>

                    <!-- Centered High-Contrast QR Code Card -->
                    <div class="flex justify-center">
                        <div class="relative inline-block">
                            <!-- Loading Skeleton -->
                            <div x-show="!qrDataUrl" class="w-56 h-56 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center gap-3">
                                <div class="w-8 h-8 border-4 border-slate-300 dark:border-slate-600 border-t-emerald-500 rounded-full animate-spin"></div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Menghasilkan QR Code...</span>
                            </div>
                            <!-- Actual QR Image -->
                            <div x-show="qrDataUrl" class="bg-white p-4 rounded-3xl shadow-xl border border-slate-200/80 inline-block transition-transform hover:scale-102">
                                <img :src="qrDataUrl" alt="WhatsApp QR Code" class="w-48 h-48 rounded-xl block">
                            </div>
                        </div>
                    </div>

                    <!-- Steps Guide -->
                    <div class="text-left bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 space-y-2.5 text-xs text-slate-600 dark:text-slate-400">
                        <p class="font-bold text-slate-900 dark:text-slate-200 flex items-center gap-1.5">
                            <i data-lucide="info" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span>Panduan Menghubungkan:</span>
                        </p>
                        <div class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">1</span>
                            <span>Buka aplikasi <strong>WhatsApp</strong> di HP Anda</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">2</span>
                            <span>Ketuk ikon <strong>⋮ Menu (Android)</strong> atau <strong>Pengaturan (iOS)</strong> &rarr; pilih <strong>Perangkat Tertaut</strong></span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">3</span>
                            <span>Ketuk <strong>Tautkan Perangkat</strong> dan arahkan kamera ke kode QR di atas</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></div>
                        <span>Sistem otomatis menyambung begitu QR berhasil dipindai...</span>
                    </div>
                </div>

                <!-- 3. DISCONNECTED STATE -->
                <div x-show="status === 'disconnected' || status === ''" x-transition.opacity class="text-center py-8 space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700">
                        <i data-lucide="smartphone-nfc" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h3 class="text-slate-900 dark:text-white font-bold text-sm">WhatsApp Belum Terhubung</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 max-w-sm mx-auto">
                            Mulai sesi baru untuk memindai kode QR dan mengaktifkan pengiriman struk digital & blast promosi.
                        </p>
                    </div>
                    <button @click="startSession()"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-sm shadow-md shadow-emerald-500/20 transition-all active:scale-98"
                        :disabled="isLoading">
                        <i data-lucide="loader-2" x-show="isLoading" class="w-4 h-4 animate-spin"></i>
                        <i data-lucide="qr-code" x-show="!isLoading" class="w-4 h-4"></i>
                        <span x-text="isLoading ? 'Menghubungkan ke Server WA...' : 'Mulai Scan QR Code'"></span>
                    </button>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: SETTINGS & TEST SENDER       -->
        <!-- ========================================== -->
        <div class="lg:col-span-5 space-y-6">

            <!-- 1. AUTO RECEIPT SETTINGS CARD -->
            <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-6 space-y-4 transition-colors">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center border border-teal-500/20">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Pengaturan Struk Digital POS</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Otomasi struk belanja ke WA pelanggan saat checkout</p>
                    </div>
                </div>

                <form action="{{ route('whatsapp.settings') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Toggle Switch -->
                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800">
                        <div class="pr-2">
                            <p class="text-xs font-bold text-slate-900 dark:text-white">Auto-Kirim Struk Checkout</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kirim struk otomatis jika data pelanggan memiliki nomor HP</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="auto_send_receipt" value="1" class="sr-only peer"
                                {{ ($waSession && $waSession->auto_send_receipt) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Receipt Footer Note -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Catatan Kaki Struk (Opsional)
                        </label>
                        <textarea name="receipt_template" rows="2" placeholder="Contoh: Terima kasih sudah berbelanja! Follow IG kami @tokoukm"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 resize-none placeholder:text-slate-400 transition-colors">{{ $waSession?->receipt_template ?? '' }}</textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs transition-colors border border-slate-200 dark:border-slate-700">
                        Simpan Pengaturan Struk
                    </button>
                </form>
            </div>

            <!-- 2. TEST SEND CONSOLE -->
            <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-6 space-y-4 transition-colors">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-500/20">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Uji Coba Kirim Pesan</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Pastikan bot WhatsApp berfungsi lancar ke nomor tujuan</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nomor HP Tujuan</label>
                        <input x-model="testPhone" type="tel" placeholder="08xxxxxxxxxx / 628xxxxxxxxxx"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-900 dark:text-white focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 placeholder:text-slate-400 transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Isi Pesan Tes</label>
                        <input x-model="testMessage" type="text" placeholder="Halo dari COOCA! 👋"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 placeholder:text-slate-400 transition-colors">
                    </div>

                    <button @click="sendTest()"
                        class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition-colors flex items-center justify-center gap-2 active:scale-98"
                        :disabled="testLoading">
                        <i data-lucide="loader-2" x-show="testLoading" class="w-3.5 h-3.5 animate-spin"></i>
                        <i data-lucide="send" x-show="!testLoading" class="w-3.5 h-3.5"></i>
                        <span x-text="testLoading ? 'Sedang Mengirim...' : 'Kirim Pesan Tes Sekarang'"></span>
                    </button>

                    <div x-show="testResult" x-text="testResult"
                        class="p-2.5 rounded-xl text-xs text-center font-bold transition-all"
                        :class="testOk ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/20'">
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function waGateway() {
    return {
        status: '{{ $waSession?->status ?? 'disconnected' }}',
        phone: '{{ $waSession?->phone_number ?? '' }}',
        deviceName: '{{ $waSession?->device_name ?? '' }}',
        qrDataUrl: null,
        isLoading: false,
        pollTimer: null,
        testPhone: '',
        testMessage: 'Halo dari {{ addslashes($business->name) }}! 👋 Terima kasih sudah menjadi pelanggan setia kami.',
        testLoading: false,
        testResult: '',
        testOk: false,

        init() {
            if (this.status !== 'connected') {
                this.pollStatus();
            }
        },

        pollStatus() {
            if (this.pollTimer) clearInterval(this.pollTimer);
            this.pollTimer = setInterval(async () => {
                try {
                    const res = await fetch('{{ route('whatsapp.qr') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                        }
                    });
                    const data = await res.json();

                    const raw = (data.status || '').toUpperCase();
                    if (raw === 'CONNECTED') {
                        this.status = 'connected';
                        clearInterval(this.pollTimer);
                        setTimeout(() => window.location.reload(), 1000);
                    } else if (raw === 'SCAN_QR') {
                        this.status = 'scan_qr';
                        this.qrDataUrl = data.qrDataUrl || null;
                    } else {
                        this.status = 'disconnected';
                    }
                } catch (e) {}
            }, 3000);
        },

        async startSession() {
            this.isLoading = true;
            try {
                const res = await fetch('{{ route('whatsapp.start') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.status = 'scan_qr';
                    this.pollStatus();
                }
            } catch (e) {} finally {
                this.isLoading = false;
            }
        },

        async disconnectWa() {
            const confirmed = await AppAlert.confirm({
                title: 'Putus Koneksi WhatsApp?',
                message: 'Yakin ingin memutus koneksi WhatsApp bisnis Anda? Sesi QR harus dipindai ulang nanti.',
                type: 'danger',
                confirmText: 'Ya, Putuskan Sesi',
                cancelText: 'Batal'
            });
            if (!confirmed) return;
            this.isLoading = true;
            try {
                await fetch('{{ route('whatsapp.disconnect') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                    }
                });
                this.status = 'disconnected';
                this.phone = '';
                this.deviceName = '';
                this.qrDataUrl = null;
            } catch (e) {} finally {
                this.isLoading = false;
            }
        },

        async sendTest() {
            if (!this.testPhone.trim() || !this.testMessage.trim()) {
                this.testResult = '⚠️ Mohon isi nomor tujuan dan pesan terlebih dahulu.';
                this.testOk = false;
                return;
            }
            this.testLoading = true;
            this.testResult = '';
            try {
                const res = await fetch('{{ route('whatsapp.test') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: JSON.stringify({ phone: this.testPhone, message: this.testMessage })
                });
                const data = await res.json();
                this.testOk = data.success ?? false;
                this.testResult = this.testOk ? '✅ Pesan tes WhatsApp berhasil terkirim!' : ('❌ Gagal: ' + (data.error || 'Server error'));
            } catch (e) {
                this.testResult = '❌ Kesalahan jaringan saat mengirim pesan.';
                this.testOk = false;
            } finally {
                this.testLoading = false;
            }
        }
    };
}
</script>
@endpush
