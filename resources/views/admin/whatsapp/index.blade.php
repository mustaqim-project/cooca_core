@extends('layouts.admin')
@section('title', 'WhatsApp Center — Pengingat Langganan & Blast Bisnis Owner')
@section('content')
<div class="space-y-6" x-data="adminWaCenter()" x-init="init()">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-[22%] bg-gradient-to-br from-[#25D366] to-[#128C7E] flex items-center justify-center shadow-[0_4px_14px_rgba(37,211,102,0.35)] shrink-0">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
            </div>
            <div>
                <h1 class="text-[20px] font-bold text-black dark:text-white tracking-tight">WhatsApp Center Administrator</h1>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Pengingat masa langganan otomatis (H-7 s/d Hari H) &amp; blast promosi ke seluruh bisnis owner</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <template x-if="status === 'connected'">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                    <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                    Bot Terhubung &nbsp;·&nbsp; <span x-text="phone ? '+' + phone : 'Aktif'"></span>
                </span>
            </template>
            <template x-if="status === 'scan_qr'">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/20">
                    <span class="w-2 h-2 rounded-full bg-[#FF9500] animate-pulse"></span>Menunggu Scan QR
                </span>
            </template>
            <template x-if="status === 'disconnected' || status === ''">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/50 dark:text-white/50 border border-black/8 dark:border-white/8">
                    <span class="w-2 h-2 rounded-full bg-black/30 dark:bg-white/30"></span>Bot Belum Terhubung
                </span>
            </template>
            <button @click="setTab('connection')" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium bg-[#25D366]/12 text-[#1A7341] dark:text-[#30D158] hover:bg-[#25D366]/18 active:scale-[0.97] transition-all inline-flex items-center gap-1.5">
                <i data-lucide="qr-code" class="w-4 h-4" stroke-width="1.5"></i>
                <span>Kelola Koneksi</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-[14px] px-4 py-3 bg-[#34C759]/10 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-[13px] font-medium flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0" stroke-width="1.5"></i>{{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-[14px] px-4 py-3 bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#C41E17] dark:text-[#FF453A] text-[13px] font-medium">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- TABS --}}
    <div class="flex items-center gap-0 border-b border-black/8 dark:border-white/10 overflow-x-auto">
        <button @click="setTab('connection')"
            :class="activeTab === 'connection' ? 'border-b-2 border-[#25D366] text-[#25D366] dark:text-[#30D158]' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white border-b-2 border-transparent'"
            class="px-4 py-2.5 text-[13px] font-semibold transition-all flex items-center gap-2 shrink-0 whitespace-nowrap">
            <i data-lucide="qr-code" class="w-4 h-4" stroke-width="1.8"></i>
            Scan QR &amp; Koneksi
            <template x-if="status === 'connected'"><span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span></template>
        </button>
        <button @click="setTab('reminders')"
            :class="activeTab === 'reminders' ? 'border-b-2 border-[#007AFF] text-[#007AFF] dark:text-[#0A84FF]' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white border-b-2 border-transparent'"
            class="px-4 py-2.5 text-[13px] font-semibold transition-all flex items-center gap-2 shrink-0 whitespace-nowrap">
            <i data-lucide="bell-ring" class="w-4 h-4" stroke-width="1.8"></i>
            Pengingat Langganan
            @if($dueData['stats']['total_pending'] > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-[#FF3B30] text-white text-[10px] font-bold">{{ $dueData['stats']['total_pending'] }}</span>
            @endif
        </button>
        <button @click="setTab('blast')"
            :class="activeTab === 'blast' ? 'border-b-2 border-[#007AFF] text-[#007AFF] dark:text-[#0A84FF]' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white border-b-2 border-transparent'"
            class="px-4 py-2.5 text-[13px] font-semibold transition-all flex items-center gap-2 shrink-0 whitespace-nowrap">
            <i data-lucide="send" class="w-4 h-4" stroke-width="1.8"></i>
            Blast Bisnis Owner
        </button>
        <button @click="setTab('templates')"
            :class="activeTab === 'templates' ? 'border-b-2 border-[#007AFF] text-[#007AFF] dark:text-[#0A84FF]' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white border-b-2 border-transparent'"
            class="px-4 py-2.5 text-[13px] font-semibold transition-all flex items-center gap-2 shrink-0 whitespace-nowrap">
            <i data-lucide="file-code-2" class="w-4 h-4" stroke-width="1.8"></i>
            Template Pesan
        </button>
    </div>

        {{-- CONNECTION --}}
        <div x-show="activeTab === 'connection'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] p-5 sm:p-6 space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[10px] bg-[#25D366]/10 text-[#25D366] flex items-center justify-center">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-[15px] font-semibold text-black dark:text-white">Koneksi WhatsApp Admin</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Sesi ini digunakan untuk pengingat dan blast bisnis owner.</p>
                        </div>
                    </div>
                    <span x-show="status === 'connected'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>Terhubung
                    </span>
                </div>

                <div x-show="status === 'connected'" class="space-y-4">
                    <div class="p-5 rounded-[14px] bg-[#34C759]/8 border border-[#34C759]/20 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-[12px] bg-[#34C759]/15 flex items-center justify-center shrink-0 text-[#34C759]"><i data-lucide="check-circle" class="w-6 h-6"></i></div>
                        <div>
                            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wide">Nomor WhatsApp Aktif</div>
                            <div class="text-[20px] font-bold text-black dark:text-white" x-text="phone ? '+' + phone : 'Sesi Aktif Terhubung'"></div>
                        </div>
                    </div>
                    <button type="button" @click="disconnectWa()" :disabled="isLoading" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 transition-all inline-flex items-center gap-1.5">
                        <i data-lucide="log-out" class="w-4 h-4"></i>Putus Koneksi Sesi
                    </button>
                </div>

                <div x-show="status === 'scan_qr'" class="text-center space-y-5 py-2">
                    <div>
                        <h3 class="text-[15px] font-semibold text-black dark:text-white">Pindai Kode QR dengan WhatsApp</h3>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Arahkan kamera WhatsApp ponsel Anda ke kode QR di bawah ini</p>
                    </div>
                    <div class="flex justify-center">
                        <div x-show="!qrDataUrl" class="w-56 h-56 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 flex flex-col items-center justify-center gap-3">
                            <div class="w-8 h-8 border-4 border-black/10 dark:border-white/10 border-t-[#25D366] rounded-full animate-spin"></div>
                            <span class="text-[12px] text-black/50 dark:text-white/50">Menghasilkan QR Code...</span>
                        </div>
                        <div x-show="qrDataUrl" class="bg-white p-4 rounded-[18px] shadow-[0_12px_32px_rgba(0,0,0,0.12)] border border-black/10">
                            <img :src="qrDataUrl" alt="WhatsApp QR Code" class="w-48 h-48 rounded-[10px] block">
                        </div>
                    </div>
                    <button type="button" @click="fetchQr()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[8px] text-[12px] font-medium bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>Muat Ulang Barcode QR
                    </button>
                </div>

                <div x-show="status === 'disconnected' || status === ''" class="text-center py-8 space-y-4">
                    <div class="w-16 h-16 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-black/30 dark:text-white/30"><i data-lucide="smartphone-nfc" class="w-8 h-8"></i></div>
                    <div>
                        <h3 class="text-black dark:text-white font-semibold text-[15px]">WhatsApp Admin Belum Terhubung</h3>
                        <p class="text-black/50 dark:text-white/50 text-[13px] mt-1">Mulai sesi baru untuk menampilkan kode QR.</p>
                    </div>
                    <button type="button" @click="startSession()" :disabled="isLoading" class="h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#25D366] hover:bg-[#1fba59] inline-flex items-center gap-2 transition-all">
                        <i data-lucide="loader-2" x-show="isLoading" class="w-4 h-4 animate-spin"></i><i data-lucide="qr-code" x-show="!isLoading" class="w-4 h-4"></i>
                        <span x-text="isLoading ? 'Menghubungkan ke Server WA...' : 'Mulai Scan QR Code'"></span>
                    </button>
                </div>

                <div x-show="status === 'connected'" class="pt-5 border-t border-black/5 dark:border-white/10 space-y-3">
                    <div>
                        <h3 class="text-[14px] font-semibold text-black dark:text-white">Uji Kirim Pesan</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Pastikan koneksi bot admin dapat mengirim pesan.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <input x-model="testPhone" type="tel" placeholder="Nomor WhatsApp tujuan" class="h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#25D366]/40">
                        <input x-model="testMessage" type="text" placeholder="Pesan tes" class="h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#25D366]/40">
                    </div>
                    <button type="button" @click="sendTest()" :disabled="testLoading" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] inline-flex items-center gap-1.5">
                        <i data-lucide="loader-2" x-show="testLoading" class="w-3.5 h-3.5 animate-spin"></i><i data-lucide="send" x-show="!testLoading" class="w-3.5 h-3.5"></i><span x-text="testLoading ? 'Mengirim...' : 'Kirim Pesan Tes'"></span>
                    </button>
                    <p x-show="testResult" x-text="testResult" class="text-[12px] font-medium" :class="testOk ? 'text-[#248A3D]' : 'text-[#C41E17]'" ></p>
                </div>
            </div>
        </div>

    <div x-show="activeTab === 'reminders'" class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div><h2 class="text-[18px] font-bold text-black dark:text-white">Pengingat Langganan</h2><p class="text-[12px] text-black/50 dark:text-white/50">Kirim pengingat pada H-7, H-3, H-1, dan hari jatuh tempo.</p></div>
            <form method="POST" action="{{ route('admin.whatsapp.reminders.send-all') }}" onsubmit="return confirm('Kirim semua pengingat yang belum terkirim?')">@csrf<button class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-semibold inline-flex items-center gap-1.5"><i data-lucide="send" class="w-4 h-4"></i>Kirim Semua Pending</button></form>
        </div>
        <div class="grid grid-cols-3 gap-3">
            @foreach([['total_due', 'Total jatuh tempo', 'text-black dark:text-white'], ['total_pending', 'Belum terkirim', 'text-[#FF9500]'], ['total_sent', 'Sudah terkirim', 'text-[#34C759]']] as [$stat, $label, $color])
                <div class="rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3"><div class="text-[20px] font-bold {{ $color }}">{{ $dueData['stats'][$stat] }}</div><div class="text-[11px] text-black/50 dark:text-white/50">{{ $label }}</div></div>
            @endforeach
        </div>
        @foreach(['h-7' => 'H-7', 'h-3' => 'H-3', 'h-1' => 'H-1', 'hari_h' => 'Hari H'] as $type => $label)
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
                <div class="px-4 py-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between"><h3 class="text-[14px] font-semibold text-black dark:text-white">{{ $label }}</h3><span class="text-[12px] text-black/50 dark:text-white/50">{{ count($dueData[$type]) }} bisnis</span></div>
                @if(count($dueData[$type]))
                    <div class="overflow-x-auto"><table class="w-full text-[12px]"><tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">@foreach($dueData[$type] as $item)<tr><td class="px-4 py-3"><div class="font-semibold text-black dark:text-white">{{ $item['business']->name }}</div><div class="text-black/50 dark:text-white/50">{{ $item['owner']?->name ?? 'Owner' }} · {{ $item['phone'] ?: 'Tanpa nomor' }}</div></td><td class="px-3 py-3 text-black/60 dark:text-white/60">{{ $item['plan_name'] }}<br>Berakhir {{ optional($item['ends_at'])->format('d/m/Y') }}</td><td class="px-3 py-3 text-right">@if($item['already_sent'])<span class="text-[#34C759] font-semibold">Terkirim</span>@elseif($item['phone'])<form method="POST" action="{{ route('admin.whatsapp.reminders.send', $item['subscription']) }}">@csrf<input type="hidden" name="type" value="{{ $type }}"><button class="text-[#007AFF] font-semibold">Kirim</button></form>@else<span class="text-[#FF3B30]">No. kosong</span>@endif</td></tr>@endforeach</tbody></table></div>
                @else
                    <div class="p-5 text-center text-[12px] text-black/45 dark:text-white/45">Tidak ada langganan pada periode ini.</div>
                @endif
            </div>
        @endforeach
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden"><div class="px-4 py-3 border-b border-black/5 dark:border-white/10"><h3 class="text-[14px] font-semibold text-black dark:text-white">Log Pengingat Terakhir</h3></div><div class="overflow-x-auto"><table class="w-full text-[12px]"><tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">@forelse($recentReminders as $log)<tr><td class="px-4 py-3 font-medium text-black dark:text-white">{{ $log->business_name }}</td><td class="px-3 py-3 text-black/60 dark:text-white/60">{{ $log->owner_name }} · {{ $log->recipient_phone }}</td><td class="px-3 py-3">{{ strtoupper($log->reminder_type) }}</td><td class="px-3 py-3">{{ $log->status === 'sent' ? 'Terkirim' : 'Gagal' }}</td><td class="px-3 py-3 text-black/45 dark:text-white/45">{{ optional($log->sent_at)->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="p-5 text-center text-black/45 dark:text-white/45">Belum ada log pengingat.</td></tr>@endforelse</tbody></table></div><div class="px-4 py-3">{{ $recentReminders->appends(['tab' => 'reminders'])->links() }}</div></div>
    </div>

    <div x-show="activeTab === 'blast'" class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        <div class="lg:col-span-2 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5"><h2 class="text-[15px] font-semibold text-black dark:text-white mb-4">Buat Blast Owner</h2><form method="POST" action="{{ route('admin.whatsapp.blasts.store') }}" class="space-y-3">@csrf<input name="title" required maxlength="255" placeholder="Judul blast" class="w-full h-9 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] px-3 text-[13px]"><select name="target_filter" class="w-full h-9 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] px-3 text-[13px]"><option value="all_owners">Semua owner ({{ $targetCounts['all_owners'] }})</option><option value="active_subscribers">Pelanggan aktif ({{ $targetCounts['active_subscribers'] }})</option><option value="expiring_soon">Akan berakhir 7 hari ({{ $targetCounts['expiring_soon'] }})</option><option value="free_tier">Free / expired ({{ $targetCounts['free_tier'] }})</option></select><textarea name="message" required maxlength="3000" rows="8" placeholder="Pesan. Placeholder: {owner}, {bisnis}, {paket}, {tanggal_habis}" class="w-full rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] p-3 text-[13px] resize-y"></textarea><input name="media_url" type="url" placeholder="URL media (opsional)" class="w-full h-9 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] px-3 text-[13px]"><button class="w-full h-9 rounded-[9px] bg-[#007AFF] text-white text-[13px] font-semibold">Kirim Blast</button></form></div>
        <div class="lg:col-span-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden"><div class="px-4 py-3 border-b border-black/5 dark:border-white/10"><h2 class="text-[15px] font-semibold text-black dark:text-white">Riwayat Blast</h2></div><div class="overflow-x-auto"><table class="w-full text-[12px]"><tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">@forelse($blasts as $blast)<tr><td class="px-4 py-3"><a href="{{ route('admin.whatsapp.blasts.show', $blast) }}" class="font-semibold text-[#007AFF]">{{ $blast->title }}</a><div class="text-black/45 dark:text-white/45">{{ optional($blast->created_at)->format('d/m/Y H:i') }}</div></td><td class="px-3 py-3">{{ $blast->total_sent }}/{{ $blast->total_recipients }} terkirim</td><td class="px-3 py-3">{{ ucfirst($blast->status) }}</td></tr>@empty<tr><td colspan="3" class="p-5 text-center text-black/45 dark:text-white/45">Belum ada blast.</td></tr>@endforelse</tbody></table></div><div class="px-4 py-3">{{ $blasts->appends(['tab' => 'blast'])->links() }}</div></div>
    </div>

    <div x-show="activeTab === 'templates'" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5"><div class="mb-4"><h2 class="text-[15px] font-semibold text-black dark:text-white">Template Pengingat</h2><p class="text-[12px] text-black/50 dark:text-white/50">Gunakan placeholder: {owner}, {bisnis}, {paket}, {tanggal_habis}, {link_bayar}.</p></div><form method="POST" action="{{ route('admin.whatsapp.reminders.templates') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">@csrf @foreach(['h-7' => ['name' => 'template_h7', 'label' => 'H-7'], 'h-3' => ['name' => 'template_h3', 'label' => 'H-3'], 'h-1' => ['name' => 'template_h1', 'label' => 'H-1'], 'hari_h' => ['name' => 'template_h0', 'label' => 'Hari H']] as $type => $field)<label class="text-[12px] font-semibold text-black/70 dark:text-white/70">{{ $field['label'] }}<textarea name="{{ $field['name'] }}" required maxlength="2000" rows="7" class="mt-1.5 w-full rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] p-3 text-[13px] font-normal resize-y">{{ $templates[$type] }}</textarea></label>@endforeach<div class="md:col-span-2"><button class="h-9 px-4 rounded-[9px] bg-[#007AFF] text-white text-[13px] font-semibold">Simpan Semua Template</button></div></form></div>

    @php
        $validTabs = ['connection', 'reminders', 'blast', 'templates'];
        $initialTab = in_array($tab, $validTabs, true) ? $tab : 'reminders';
        $initialPhone = $waStatus['phone'] ?? '';
        if (!$initialPhone && isset($waStatus['user']['id'])) {
            $initialPhone = explode(':', (string) $waStatus['user']['id'])[0];
        }
    @endphp

    @push('scripts')
    <script>
    function adminWaCenter() {
        return {
            activeTab: @json($initialTab),
            status: @json($liveStatus ?? 'disconnected'),
            phone: @json($initialPhone),
            qrDataUrl: @json($qrDataUrl ?? null),
            isLoading: false,
            pollTimer: null,
            testPhone: '',
            testMessage: 'Pesan tes dari WhatsApp Center Cooca.',
            testLoading: false,
            testResult: '',
            testOk: false,

            init() {
                this.checkStatus();
            },

            setTab(tab) {
                this.activeTab = tab;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
                if (tab === 'connection') this.checkStatus();
            },

            async checkStatus() {
                try {
                    const response = await fetch('{{ route('admin.whatsapp.status') }}', { headers: { 'Accept': 'application/json' } });
                    const data = await response.json();
                    this.applyStatus(data);
                    if (this.status === 'scan_qr') this.pollStatus();
                } catch (error) {
                    this.status = 'disconnected';
                }
            },

            applyStatus(data) {
                const rawStatus = String(data.status || '').toLowerCase();
                this.status = rawStatus === 'connected' ? 'connected' : (rawStatus === 'scan_qr' || data.qrDataUrl ? 'scan_qr' : 'disconnected');
                this.phone = data.phone || this.phone;
                if (data.qrDataUrl) this.qrDataUrl = data.qrDataUrl;
                if (this.status === 'connected' && this.pollTimer) clearInterval(this.pollTimer);
            },

            async fetchQr() {
                try {
                    const response = await fetch('{{ route('admin.whatsapp.qr') }}', { headers: { 'Accept': 'application/json' } });
                    this.applyStatus(await response.json());
                } catch (error) {}
            },

            pollStatus() {
                if (this.pollTimer) clearInterval(this.pollTimer);
                this.pollTimer = setInterval(() => this.fetchQr(), 2500);
            },

            async startSession() {
                this.isLoading = true;
                this.qrDataUrl = null;
                try {
                    const response = await fetch('{{ route('admin.whatsapp.start') }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }
                    });
                    const data = await response.json();
                    if (!response.ok || data.success === false) throw new Error(data.error || 'Gagal memulai sesi WhatsApp.');
                    this.status = 'scan_qr';
                    await this.fetchQr();
                    this.pollStatus();
                } catch (error) {
                    this.status = 'disconnected';
                    this.testResult = error.message;
                    this.testOk = false;
                } finally {
                    this.isLoading = false;
                }
            },

            async sendTest() {
                if (!this.testPhone.trim() || !this.testMessage.trim()) {
                    this.testResult = 'Nomor dan pesan wajib diisi.';
                    this.testOk = false;
                    return;
                }
                this.testLoading = true;
                this.testResult = '';
                try {
                    const response = await fetch('{{ route('admin.whatsapp.test') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                        body: JSON.stringify({ phone: this.testPhone, message: this.testMessage })
                    });
                    const data = await response.json();
                    this.testOk = response.ok && data.success === true;
                    this.testResult = this.testOk ? 'Pesan tes berhasil dikirim.' : ('Gagal: ' + (data.error || 'server WhatsApp tidak merespons.'));
                } catch (error) {
                    this.testOk = false;
                    this.testResult = 'Kesalahan jaringan saat mengirim pesan.';
                } finally {
                    this.testLoading = false;
                }
            },

            async disconnectWa() {
                try {
                    await fetch('{{ route('admin.whatsapp.disconnect') }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }
                    });
                    this.status = 'disconnected';
                    this.phone = '';
                    this.qrDataUrl = null;
                    if (this.pollTimer) clearInterval(this.pollTimer);
                } catch (error) {}
            }
        };
    }
    </script>
    @endpush

</div>
@endsection
