@extends('layouts.app', ['title' => __('pos.printers_title')])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
    showAddModal: false,
    showEditModal: false,
    showManualDrawerModal: false,
    isTesting: false,
    testFeedback: '',
    testFeedbackSuccess: true,
    editPrinter: {
        id: '',
        name: '',
        location_id: '',
        connection_type: 'lan',
        interface_address: '',
        port: 9100,
        paper_width: '80mm',
        character_set: 'CP437',
        is_active: true,
        is_default: false,
        capabilities: ['print_text', 'print_image', 'barcode', 'qr_code', 'cut', 'cash_drawer'],
        assigned_usages: ['cashier_receipt'],
        assigned_category_ids: []
    },
    manualDrawer: {
        printer_id: '',
        supervisor_pin: '',
        reason: ''
    },
    openEdit(p) {
        this.editPrinter = {
            id: p.id,
            name: p.name,
            location_id: p.location_id || '',
            connection_type: p.connection_type || 'lan',
            interface_address: p.interface_address || '',
            port: p.port || 9100,
            paper_width: p.paper_width || '80mm',
            character_set: p.character_set || 'CP437',
            is_active: Boolean(p.is_active),
            is_default: Boolean(p.is_default),
            capabilities: p.capabilities || ['print_text', 'print_image', 'barcode', 'qr_code', 'cut', 'cash_drawer'],
            assigned_usages: p.assigned_usages || ['cashier_receipt'],
            assigned_category_ids: p.assigned_category_ids || []
        };
        this.showEditModal = true;
    },
    openManualDrawer(p) {
        this.manualDrawer.printer_id = p.id;
        this.manualDrawer.supervisor_pin = '';
        this.manualDrawer.reason = '';
        this.showManualDrawerModal = true;
    },
    isSubmitting: false,
    async runTestPrint(printerId) {
        this.isTesting = true;
        this.testFeedback = '';
        try {
            const res = await fetch(`{{ url('/pos/printers') }}/${printerId}/test-print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            this.testFeedbackSuccess = data.success;
            this.testFeedback = data.message || (data.success ? 'Tes cetak berhasil dikirim.' : 'Tes cetak gagal.');
            if (window.AppAlert) {
                if (data.success) {
                    window.AppAlert.success(this.testFeedback);
                } else {
                    window.AppAlert.error(this.testFeedback);
                }
            }
        } catch (e) {
            this.testFeedbackSuccess = false;
            this.testFeedback = 'Gagal mengirim perintah tes cetak: ' + e.message;
            if (window.AppAlert) {
                window.AppAlert.error(this.testFeedback);
            }
        } finally {
            this.isTesting = false;
        }
    },
    async runTestDrawer(printerId) {
        if (window.AppAlert) {
            const confirmed = await window.AppAlert.confirm({
                title: 'Uji Sinyal Laci Kas',
                message: 'Kirim sinyal pulse pembukaan laci kas fisik (Cash Drawer Pulse)? Pastikan laci uang terhubung dengan kabel RJ11/RJ12 ke printer.',
                type: 'warning',
                confirmText: 'Kirim Sinyal Laci',
                cancelText: 'Batal'
            });
            if (!confirmed) return;
        }
        this.isTesting = true;
        this.testFeedback = '';
        try {
            const res = await fetch(`{{ url('/pos/printers') }}/${printerId}/test-drawer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            this.testFeedbackSuccess = data.success;
            this.testFeedback = data.message || (data.success ? 'Sinyal pulse laci kas berhasil dikirim.' : 'Gagal membuka laci kas.');
            if (window.AppAlert) {
                if (data.success) {
                    window.AppAlert.success(this.testFeedback);
                } else {
                    window.AppAlert.error(this.testFeedback);
                }
            }
        } catch (e) {
            this.testFeedbackSuccess = false;
            this.testFeedback = 'Kesalahan uji coba laci kas: ' + e.message;
            if (window.AppAlert) {
                window.AppAlert.error(this.testFeedback);
            }
        } finally {
            this.isTesting = false;
        }
    },
    async runDiagnose(printerId) {
        this.isTesting = true;
        this.testFeedback = '';
        try {
            const res = await fetch(`{{ url('/pos/printers') }}/${printerId}/diagnose`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            this.testFeedbackSuccess = data.connected;
            this.testFeedback = data.message;
            if (window.AppAlert) {
                if (data.connected) {
                    window.AppAlert.success(data.message || 'Diagnostik sukses: Printer terhubung.');
                } else {
                    window.AppAlert.error(data.message || 'Diagnostik gagal: Printer tidak merespons.');
                }
            }
        } catch (e) {
            this.testFeedbackSuccess = false;
            this.testFeedback = 'Gagal melakukan diagnostik: ' + e.message;
            if (window.AppAlert) {
                window.AppAlert.error(this.testFeedback);
            }
        } finally {
            this.isTesting = false;
        }
    },
    async submitManualDrawer() {
        if (!this.manualDrawer.supervisor_pin || !this.manualDrawer.reason) {
            if (window.AppAlert) {
                window.AppAlert.error('PIN Supervisor dan Alasan pembukaan laci kas wajib diisi.');
            }
            return;
        }
        this.isTesting = true;
        try {
            const res = await fetch(`{{ route('pos.cash-drawer.manual-pop') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.manualDrawer)
            });
            const data = await res.json();
            if (data.success) {
                this.showManualDrawerModal = false;
                this.testFeedbackSuccess = true;
                this.testFeedback = 'Laci kas berhasil dibuka secara manual (Tercatat di Audit Log).';
                if (window.AppAlert) {
                    window.AppAlert.success(this.testFeedback);
                }
            } else {
                if (window.AppAlert) {
                    window.AppAlert.error(data.message || 'Gagal membuka laci kas.');
                }
            }
        } catch (e) {
            if (window.AppAlert) {
                window.AppAlert.error('Terjadi kesalahan: ' + e.message);
            }
        } finally {
            this.isTesting = false;
        }
    }
}">

    {{-- MODULE HEADER & PERSISTENT POS TABS --}}
    <x-module-header
        module="pos"
        :title="__('pos.printers_title')"
        :subtitle="__('pos.printers_subtitle')">
        <x-slot:actions>
            @if(\App\Support\Context::hasPermission('pos.terminal'))
                <a href="{{ route('pos.terminal') }}" class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                    <span>Terminal POS</span>
                </a>
            @endif

            <button type="button" @click="showAddModal = true" class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer w-full sm:w-auto">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Printer</span>
            </button>
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="pos" />

    <!-- Feedback Toast -->
    <div x-show="testFeedback" x-cloak
        class="p-3.5 rounded-[12px] border text-[13px] font-medium flex items-center justify-between gap-3 shadow-xs transition-all"
        :class="testFeedbackSuccess ? 'bg-[#34C759]/12 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/12 border-[#FF3B30]/30 text-[#C41E17] dark:text-[#FF453A]'">
        <div class="flex items-center gap-2">
            <i :data-lucide="testFeedbackSuccess ? 'check-circle-2' : 'alert-triangle'" class="w-4 h-4 shrink-0"></i>
            <span x-text="testFeedback"></span>
        </div>
        <button type="button" @click="testFeedback = ''" class="p-1 rounded-md opacity-60 hover:opacity-100 hover:bg-black/5 dark:hover:bg-white/10 transition">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>

    <!-- Flash message -->
    @if(session('success'))
    <div class="p-3 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-[13px] font-medium flex items-center gap-2 shadow-xs">
        <i data-lucide="check" class="w-4 h-4"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. BENTO KPI CARDS STRIP                               -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Printers -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-medium">Total Terdaftar</span>
                <div class="w-8 h-8 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center">
                    <i data-lucide="printer" class="w-4 h-4 text-black/70 dark:text-white/70"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums">{{ $totalPrinters }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Perangkat aktif</div>
            </div>
        </div>

        <!-- Online Printers -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-[#34C759]">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Status Terhubung</span>
                <div class="w-8 h-8 rounded-xl bg-[#34C759]/15 flex items-center justify-center">
                    <i data-lucide="wifi" class="w-4 h-4 text-[#34C759]"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold text-[#34C759] tabular-nums">{{ $onlinePrinters }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Siap mencetak instan</div>
            </div>
        </div>

        <!-- Cashier Printers -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-[#007AFF]">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Printer Kasir</span>
                <div class="w-8 h-8 rounded-xl bg-[#007AFF]/15 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4 text-[#007AFF]"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold text-[#007AFF] tabular-nums">{{ $cashierPrinters }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Struk &amp; Laci Uang</div>
            </div>
        </div>

        <!-- Kitchen / Bar Printers -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-[#FF9500]">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Printer Dapur &amp; Bar</span>
                <div class="w-8 h-8 rounded-xl bg-[#FF9500]/15 flex items-center justify-center">
                    <i data-lucide="utensils" class="w-4 h-4 text-[#FF9500]"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold text-[#FF9500] tabular-nums">{{ $kitchenPrinters }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">KOT pesanan ter-routing</div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. OUTLET FILTER & PRINTERS LIST                       -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-4 shadow-xs">
        <!-- Filter Tabs per Outlet -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
            <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04]">
                <a href="{{ route('pos.printers.index') }}"
                    class="px-3 py-1.5 rounded-[9px] text-[12px] font-medium transition {{ empty($selectedLocationId) ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    Semua Outlet
                </a>
                @foreach($locations as $loc)
                <a href="{{ route('pos.printers.index', ['location_id' => $loc->id]) }}"
                    class="px-3 py-1.5 rounded-[9px] text-[12px] font-medium transition {{ $selectedLocationId === $loc->id ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    {{ $loc->name }}
                </a>
                @endforeach
            </div>

            <div class="text-[12px] text-black/45 dark:text-white/45">
                Total: <span class="font-bold text-black dark:text-white tabular-nums">{{ $printers->count() }}</span> Printer
            </div>
        </div>

        @if($printers->isEmpty())
        <!-- Empty State -->
        <div class="text-center py-12 space-y-3">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/40 dark:text-white/40">
                <i data-lucide="printer" class="w-7 h-7"></i>
            </div>
            <div class="space-y-1">
                <h3 class="text-[15px] font-bold text-black dark:text-white">Belum Ada Printer Terdaftar</h3>
                <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm mx-auto">Tambahkan printer thermal kasir (USB, LAN, Bluetooth) atau printer dapur untuk mulai mencetak struk secara instan.</p>
            </div>
            <button type="button" @click="showAddModal = true" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all inline-flex items-center gap-1.5 shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Printer Pertama</span>
            </button>
        </div>
        @else

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 text-[11px] uppercase tracking-wider font-semibold">
                        <th class="pb-3 pl-1">Perangkat / Nama</th>
                        <th class="pb-3">Koneksi &amp; Alamat</th>
                        <th class="pb-3">Fungsi / Pos</th>
                        <th class="pb-3">Lebar Kertas</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3 text-right pr-1">Aksi &amp; Diagnostik</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @foreach($printers as $p)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                        <td class="py-3.5 pl-1">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-black/[0.05] dark:bg-white/[0.08] flex items-center justify-center shrink-0">
                                    <i data-lucide="printer" class="w-4 h-4 text-black/70 dark:text-white/70"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                                        <span>{{ $p->name }}</span>
                                        @if($p->is_default)
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-[#007AFF]/15 text-[#007AFF]">Default</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">{{ $p->location?->name ?? 'Semua Outlet' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5">
                            <div class="font-mono text-[12px] text-black/80 dark:text-white/80">{{ $p->interface_address }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 uppercase tracking-wide">
                                {{ strtoupper($p->connection_type) }} @if(in_array($p->connection_type, ['lan','wifi']))(Port {{ $p->port }})@endif
                            </div>
                        </td>
                        <td class="py-3.5">
                            <div class="flex flex-wrap gap-1">
                                @if($p->supportsUsage('cashier_receipt'))
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#007AFF]/12 text-[#007AFF]">Kasir Struk</span>
                                @endif
                                @if($p->supportsUsage('kitchen_order'))
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#FF9500]/12 text-[#FF9500]">Dapur (KOT)</span>
                                @endif
                                @if($p->supportsUsage('bar_order'))
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#AF52DE]/12 text-[#AF52DE]">Bar Minuman</span>
                                @endif
                                @if($p->supportsUsage('shift_report'))
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#34C759]/12 text-[#34C759]">Tutup Shift</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 font-mono text-[12px] font-bold text-black/70 dark:text-white/70">
                            {{ $p->paper_width }}
                        </td>
                        <td class="py-3.5">
                            @if($p->last_status === 'online')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                Online
                            </span>
                            @elseif($p->last_status === 'offline')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                Offline
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-black/40 dark:bg-white/40"></span>
                                Belum Dicek
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 text-right pr-1">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Test Print -->
                                <button type="button" @click="runTestPrint('{{ $p->id }}')" :disabled="isTesting"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 active:scale-[0.97] transition flex items-center gap-1"
                                    title="Tes Cetak Sampel ESC/POS">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Tes Cetak</span>
                                </button>

                                <!-- Test Drawer (if supported) -->
                                @if($p->hasCapability('cash_drawer'))
                                <button type="button" @click="runTestDrawer('{{ $p->id }}')" :disabled="isTesting"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 active:scale-[0.97] transition flex items-center gap-1"
                                    title="Uji Sinyal Laci Kas (Cash Drawer Pulse)">
                                    <i data-lucide="inbox" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Tes Laci</span>
                                </button>
                                <button type="button" @click="openManualDrawer({{ json_encode($p) }})"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 active:scale-[0.97] transition flex items-center gap-1"
                                    title="Buka Laci Kas Manual (Wajib PIN Supervisor)">
                                    <i data-lucide="key" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>Buka Manual</span>
                                </button>
                                @endif

                                <!-- Ping Diagnostic -->
                                <button type="button" @click="runDiagnose('{{ $p->id }}')" :disabled="isTesting"
                                    class="h-8 w-8 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/70 dark:text-white/70 flex items-center justify-center transition"
                                    title="Diagnosa Ping Koneksi">
                                    <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                                </button>

                                <!-- Edit -->
                                <button type="button" @click="openEdit({{ json_encode($p) }})"
                                    class="h-8 w-8 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/70 dark:text-white/70 flex items-center justify-center transition"
                                    title="Edit Pengaturan">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('pos.printers.destroy', $p->id) }}" class="inline"
                                    onsubmit="return typeof AppAlert !== 'undefined' ? AppAlert.confirmSubmit(event, this, 'Hapus printer \'{{ addslashes($p->name) }}\'?', 'Hapus Printer?', 'danger') : true">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-8 w-8 rounded-[8px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] flex items-center justify-center transition" title="Hapus Printer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List View -->
        <div class="block md:hidden space-y-3">
            @foreach($printers as $p)
            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="font-bold text-[14px] text-black dark:text-white flex items-center gap-1.5 truncate">
                            <span>{{ $p->name }}</span>
                            @if($p->is_default)
                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-[#007AFF]/15 text-[#007AFF] shrink-0">Default</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $p->location?->name ?? 'Semua Outlet' }} • {{ $p->paper_width }}</div>
                    </div>
                    @if($p->last_status === 'online')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] shrink-0">Online</span>
                    @elseif($p->last_status === 'offline')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A] shrink-0">Offline</span>
                    @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/[0.06] text-black/60 shrink-0">Belum Dicek</span>
                    @endif
                </div>

                <div class="text-[12px] font-mono p-2 rounded-lg bg-black/[0.03] dark:bg-white/[0.04] text-black/80 dark:text-white/80">
                    {{ strtoupper($p->connection_type) }}: {{ $p->interface_address }} @if(in_array($p->connection_type, ['lan','wifi']))({{ $p->port }})@endif
                </div>

                <div class="flex flex-wrap gap-1.5">
                    @if($p->supportsUsage('cashier_receipt'))
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-[#007AFF]/12 text-[#007AFF]">Kasir</span>
                    @endif
                    @if($p->supportsUsage('kitchen_order'))
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-[#FF9500]/12 text-[#FF9500]">Dapur (KOT)</span>
                    @endif
                    @if($p->supportsUsage('bar_order'))
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-[#AF52DE]/12 text-[#AF52DE]">Bar</span>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="runTestPrint('{{ $p->id }}')" class="h-8 px-2.5 rounded-lg text-[12px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 flex items-center gap-1">
                            <i data-lucide="printer" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Tes</span>
                        </button>
                        @if($p->hasCapability('cash_drawer'))
                        <button type="button" @click="runTestDrawer('{{ $p->id }}')" class="min-h-[36px] px-2.5 rounded-lg text-[12px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 flex items-center gap-1">
                            <i data-lucide="inbox" class="w-3.5 h-3.5 text-[#34C759]"></i>
                            <span>Laci</span>
                        </button>
                        <button type="button" @click="openManualDrawer({{ json_encode($p) }})" class="min-h-[36px] px-2.5 rounded-lg text-[12px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 flex items-center gap-1">
                            <i data-lucide="key" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                            <span>Buka</span>
                        </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="openEdit({{ json_encode($p) }})" class="h-8 px-2.5 rounded-lg text-[12px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('pos.printers.destroy', $p->id) }}" class="inline"
                            onsubmit="return typeof AppAlert !== 'undefined' ? AppAlert.confirmSubmit(event, this, 'Hapus printer \'{{ addslashes($p->name) }}\'?', 'Hapus Printer?', 'danger') : true">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="h-8 px-2.5 rounded-lg text-[12px] font-medium bg-[#FF3B30]/10 text-[#FF3B30]">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. LOCAL POS AGENT BRIDGE ARCHITECTURE BANNER         -->
    <!-- ===================================================== -->
    <div class="p-5 rounded-[20px] bg-gradient-to-br from-black/[0.02] to-black/[0.05] dark:from-white/[0.02] dark:to-white/[0.05] border border-black/5 dark:border-white/10 space-y-3">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                <i data-lucide="cpu" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[14px] font-bold text-black dark:text-white">COOCA Local POS Hardware Agent (Bridge USB &amp; Bluetooth)</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Hubungkan printer USB lokal atau Bluetooth portable langsung dari browser tanpa pop-up dialog.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-1 text-[12px]">
            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <div class="font-bold text-[#007AFF] flex items-center gap-1">
                    <span>1. Printer LAN / Wi-Fi</span>
                </div>
                <p class="text-black/60 dark:text-white/60 text-[11px]">Server COOCA langsung mencetak via raw socket port 9100 tanpa perlu instalasi aplikasi tambahan.</p>
            </div>

            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <div class="font-bold text-[#34C759] flex items-center gap-1">
                    <span>2. Printer USB / Windows</span>
                </div>
                <p class="text-black/60 dark:text-white/60 text-[11px]">Gunakan Windows Spooler Share (contoh: <code>POS-80</code>) atau jalankan COOCA Local POS Agent di port <code>9898</code>.</p>
            </div>

            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <div class="font-bold text-[#FF9500] flex items-center gap-1">
                    <span>3. Bluetooth Portable</span>
                </div>
                <p class="text-black/60 dark:text-white/60 text-[11px]">Pairing printer Bluetooth di Windows/Android, lalu petakan ke Virtual COM port atau kirim via Web Bluetooth.</p>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. MODAL: TAMBAH PRINTER (Apple Sheet Style XXL)      -->
    <!-- ===================================================== -->
    <div x-show="showAddModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-md p-4 overflow-y-auto"
        @keydown.escape.window="showAddModal = false">
        <div class="w-full max-w-5xl xl:max-w-6xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/15 p-6 sm:p-7 space-y-6 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white max-h-[92vh] overflow-y-auto"
            @click.outside="showAddModal = false">

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold">
                        <i data-lucide="printer" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[19px] tracking-tight">Tambah Printer &amp; Perangkat Keras Baru</h3>
                        <p class="text-[13px] text-black/50 dark:text-white/50">Konfigurasikan printer thermal ESC/POS, cash drawer, atau KOT routing</p>
                    </div>
                </div>
                <button type="button" @click="showAddModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('pos.printers.store') }}" @submit="isSubmitting = true" class="space-y-6" x-data="{ connType: 'lan' }">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left Column: Primary Config (7 Cols) -->
                    <div class="lg:col-span-7 space-y-4">
                        <!-- Nama Printer -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama Printer *</label>
                            <input type="text" name="name" required placeholder="Contoh: Kasir Utama, Printer Dapur, Barista"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <!-- Outlet / Lokasi -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Outlet / Lokasi Operasional</label>
                            <select name="location_id" class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <option value="">Semua Outlet (Global)</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tipe Koneksi & Lebar Kertas -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tipe Koneksi *</label>
                                <select name="connection_type" x-model="connType" required class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="lan">Kabel LAN (Ethernet TCP/IP)</option>
                                    <option value="wifi">Wi-Fi (Wireless TCP/IP)</option>
                                    <option value="windows">Windows Shared Printer / Spooler</option>
                                    <option value="usb">USB Direct / Device Path</option>
                                    <option value="bluetooth">Bluetooth (via Local Agent / COM)</option>
                                    <option value="serial">Serial COM Port</option>
                                    <option value="agent">Local POS Agent (Bridge 9898)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Lebar Kertas Thermal *</label>
                                <select name="paper_width" required class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="80mm">80mm (Standar Kasir POS / 48 Kolom)</option>
                                    <option value="58mm">58mm (Mobile Portable / 32 Kolom)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Interface Address & Port -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div class="sm:col-span-2">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    <span x-show="connType === 'lan' || connType === 'wifi'">Alamat IP LAN / Wi-Fi *</span>
                                    <span x-show="connType === 'windows'">Nama Share Printer Windows *</span>
                                    <span x-show="connType === 'usb' || connType === 'serial'">Path Port / Device *</span>
                                    <span x-show="connType === 'bluetooth' || connType === 'agent'">Identifier Bluetooth / Agen *</span>
                                </label>
                                <input type="text" name="interface_address" required
                                    :placeholder="connType === 'lan' || connType === 'wifi' ? '192.168.1.200' : (connType === 'windows' ? 'POS-80' : '/dev/usb/lp0')"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Port (Raw Socket)</label>
                                <input type="number" name="port" value="9100" min="1" max="65535"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                        </div>

                        <!-- Default Printer Toggle -->
                        <div class="flex items-center gap-2.5 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <input type="checkbox" name="is_default" value="1" id="add_is_default" class="w-4 h-4 rounded text-[#007AFF]">
                            <label for="add_is_default" class="text-[13px] font-medium text-black/80 dark:text-white/80 cursor-pointer">
                                Jadikan sebagai printer kasir utama (Default)
                            </label>
                        </div>
                    </div>

                    <!-- Right Column: Capabilities & Security Info (5 Cols) -->
                    <div class="lg:col-span-5 space-y-4">
                        <!-- Capabilities -->
                        <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2.5">
                            <label class="block text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">Kemampuan Hardware</label>
                            <div class="grid grid-cols-2 gap-2 text-[12px]">
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="capabilities[]" value="cut" checked class="rounded text-[#007AFF]">
                                    <span>Auto-Cut</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="capabilities[]" value="cash_drawer" checked class="rounded text-[#007AFF]">
                                    <span>Laci Uang</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="capabilities[]" value="qr_code" checked class="rounded text-[#007AFF]">
                                    <span>QR Code</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="capabilities[]" value="barcode" checked class="rounded text-[#007AFF]">
                                    <span>Barcode 1D</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer col-span-2">
                                    <input type="checkbox" name="capabilities[]" value="beep" class="rounded text-[#007AFF]">
                                    <span>Buzzer / Audio Beep</span>
                                </label>
                            </div>
                        </div>

                        <!-- Assigned Usages -->
                        <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2.5">
                            <label class="block text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">Fungsi / Routing Cetak</label>
                            <div class="grid grid-cols-2 gap-2 text-[12px]">
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="assigned_usages[]" value="cashier_receipt" checked class="rounded text-[#007AFF]">
                                    <span>Struk Kasir</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="assigned_usages[]" value="kitchen_order" class="rounded text-[#007AFF]">
                                    <span>Tiket Dapur</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="assigned_usages[]" value="bar_order" class="rounded text-[#007AFF]">
                                    <span>Tiket Bar</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="assigned_usages[]" value="shift_report" checked class="rounded text-[#007AFF]">
                                    <span>Tutup Shift</span>
                                </label>
                            </div>
                        </div>

                        <!-- Security Whitelist Info -->
                        <div class="p-3.5 rounded-[14px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/15 text-[11px] text-black/70 dark:text-white/70 space-y-1">
                            <div class="font-bold text-[#007AFF] flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                <span>Proteksi Keamanan Siber (SSRF Guard)</span>
                            </div>
                            <p>IP LAN wajib menggunakan subnet privat (192.168.x.x / 10.x.x.x / 172.16-31.x.x) dengan port standar 9100, 9898, 515, 631, atau 8080.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showAddModal = false" class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 hover:bg-black/[0.1] transition">Batal</button>
                    <button type="submit" :disabled="isSubmitting" class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-semibold bg-[#007AFF] hover:bg-[#0071E3] text-white transition shadow-sm flex items-center gap-2">
                        <template x-if="isSubmitting">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        </template>
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Profil Printer'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: EDIT PRINTER (Apple Sheet Style XXL)         -->
    <!-- ===================================================== -->
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-md p-4 overflow-y-auto"
        @keydown.escape.window="showEditModal = false">
        <div class="w-full max-w-5xl xl:max-w-6xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/15 p-6 sm:p-7 space-y-6 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white max-h-[92vh] overflow-y-auto"
            @click.outside="showEditModal = false">

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[19px] tracking-tight">Edit Profil Printer &amp; Hardware</h3>
                        <p class="text-[13px] text-black/50 dark:text-white/50">Perbarui konfigurasi interface, kemampuan perangkat, dan penugasan fungsi cetak</p>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" :action="`{{ url('/pos/printers') }}/${editPrinter.id}`" @submit="isSubmitting = true" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left Column: Primary Config (7 Cols) -->
                    <div class="lg:col-span-7 space-y-4">
                        <!-- Nama Printer -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama Printer *</label>
                            <input type="text" name="name" x-model="editPrinter.name" required
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <!-- Outlet / Lokasi -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Outlet / Lokasi Operasional</label>
                            <select name="location_id" x-model="editPrinter.location_id" class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <option value="">Semua Outlet (Global)</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tipe Koneksi & Lebar Kertas -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tipe Koneksi *</label>
                                <select name="connection_type" x-model="editPrinter.connection_type" required class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="lan">Kabel LAN (Ethernet TCP/IP)</option>
                                    <option value="wifi">Wi-Fi (Wireless TCP/IP)</option>
                                    <option value="windows">Windows Shared Printer / Spooler</option>
                                    <option value="usb">USB Direct / Device Path</option>
                                    <option value="bluetooth">Bluetooth (via Local Agent / COM)</option>
                                    <option value="serial">Serial COM Port</option>
                                    <option value="agent">Local POS Agent (Bridge 9898)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Lebar Kertas Thermal *</label>
                                <select name="paper_width" x-model="editPrinter.paper_width" required class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="80mm">80mm (Standar POS / 48 Kolom)</option>
                                    <option value="58mm">58mm (Mobile Portable / 32 Kolom)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Interface Address & Port -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div class="sm:col-span-2">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Alamat IP / Nama Share / Port *</label>
                                <input type="text" name="interface_address" x-model="editPrinter.interface_address" required
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Port</label>
                                <input type="number" name="port" x-model="editPrinter.port" min="1" max="65535"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                        </div>

                        <!-- Default Printer Toggle -->
                        <div class="flex items-center gap-2.5 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <input type="checkbox" name="is_default" value="1" id="edit_is_default" x-model="editPrinter.is_default" class="w-4 h-4 rounded text-[#007AFF]">
                            <label for="edit_is_default" class="text-[13px] font-medium text-black/80 dark:text-white/80 cursor-pointer">
                                Jadikan sebagai printer kasir utama (Default)
                            </label>
                        </div>
                    </div>

                    <!-- Right Column: Capabilities & Usages (5 Cols) -->
                    <div class="lg:col-span-5 space-y-4">
                        <!-- Capabilities -->
                        <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2.5">
                            <label class="block text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">Kemampuan Hardware</label>
                            <div class="grid grid-cols-2 gap-2 text-[12px]">
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="capabilities[]" value="cut" :checked="editPrinter.capabilities.includes('cut')" class="rounded text-[#007AFF]">
                                    <span>Auto-Cut</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="capabilities[]" value="cash_drawer" :checked="editPrinter.capabilities.includes('cash_drawer')" class="rounded text-[#007AFF]">
                                    <span>Laci Uang</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="capabilities[]" value="qr_code" :checked="editPrinter.capabilities.includes('qr_code')" class="rounded text-[#007AFF]">
                                    <span>QR Code</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="capabilities[]" value="barcode" :checked="editPrinter.capabilities.includes('barcode')" class="rounded text-[#007AFF]">
                                    <span>Barcode 1D</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer col-span-2">
                                    <input type="checkbox" name="capabilities[]" value="beep" :checked="editPrinter.capabilities.includes('beep')" class="rounded text-[#007AFF]">
                                    <span>Buzzer / Audio Beep</span>
                                </label>
                            </div>
                        </div>

                        <!-- Assigned Usages -->
                        <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2.5">
                            <label class="block text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">Fungsi / Routing Cetak</label>
                            <div class="grid grid-cols-2 gap-2 text-[12px]">
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="assigned_usages[]" value="cashier_receipt" :checked="editPrinter.assigned_usages.includes('cashier_receipt')" class="rounded text-[#007AFF]">
                                    <span>Struk Kasir</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="assigned_usages[]" value="kitchen_order" :checked="editPrinter.assigned_usages.includes('kitchen_order')" class="rounded text-[#007AFF]">
                                    <span>Tiket Dapur</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="assigned_usages[]" value="bar_order" :checked="editPrinter.assigned_usages.includes('bar_order')" class="rounded text-[#007AFF]">
                                    <span>Tiket Bar</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 cursor-pointer">
                                    <input type="checkbox" name="assigned_usages[]" value="shift_report" :checked="editPrinter.assigned_usages.includes('shift_report')" class="rounded text-[#007AFF]">
                                    <span>Tutup Shift</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showEditModal = false" class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 hover:bg-black/[0.1] transition">Batal</button>
                    <button type="submit" :disabled="isSubmitting" class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-semibold bg-[#007AFF] hover:bg-[#0071E3] text-white transition shadow-sm flex items-center gap-2">
                        <template x-if="isSubmitting">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        </template>
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 7. MANUAL CASH DRAWER POP MODAL (Supervisor PIN XXL)  -->
    <!-- ===================================================== -->
    <div x-show="showManualDrawerModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-md p-4 overflow-y-auto"
        @keydown.escape.window="showManualDrawerModal = false">
        <div class="w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 sm:p-7 space-y-6 shadow-[0_25px_60px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/15 max-h-[92vh] overflow-y-auto"
            @click.away="showManualDrawerModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/15 flex items-center justify-center text-[#FF9500]">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[19px] font-bold text-black dark:text-white tracking-tight">Buka Laci Kas Manual (No-Sale Pop)</h3>
                        <p class="text-[13px] text-black/50 dark:text-white/50">Otorisasi Supervisor &amp; Audit Log Anti-Fraud</p>
                    </div>
                </div>
                <button type="button" @click="showManualDrawerModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-4 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#995B00] dark:text-[#FFB340] text-[12px] leading-relaxed flex items-start gap-3">
                <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                <span>Setiap pembukaan laci kas tanpa transaksi penjualan dicatat secara permanen dalam audit log sistem dengan identitas kasir, waktu, dan alasan untuk mencegah selisih kas fisik.</span>
            </div>

            <form @submit.prevent="submitManualDrawer" class="space-y-4">
                <div>
                    <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">PIN Supervisor / Owner <span class="text-red-500">*</span></label>
                    <input type="password" x-model="manualDrawer.supervisor_pin" required maxlength="8" placeholder="Masukkan 4-8 digit PIN otorisasi"
                        class="w-full h-12 px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[18px] text-black dark:text-white tracking-widest font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">Alasan Pembukaan Laci <span class="text-red-500">*</span></label>
                    <textarea x-model="manualDrawer.reason" required rows="3" placeholder="Contoh: Penukaran uang kembalian pecahan kecil dengan kasir sebelah..."
                        class="w-full p-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 resize-none transition"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showManualDrawerModal = false" class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 hover:bg-black/[0.1] transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isTesting" class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-semibold bg-[#FF9500] hover:bg-[#E08500] text-white transition shadow-sm flex items-center justify-center gap-2">
                        <template x-if="isTesting">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        </template>
                        <i x-show="!isTesting" data-lucide="unlock" class="w-4 h-4"></i>
                        <span x-text="isTesting ? 'Mengirim Sinyal...' : 'Otorisasi &amp; Buka Laci'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
