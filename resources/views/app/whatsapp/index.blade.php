@extends('layouts.app')

@section('title', 'WhatsApp Gateway — ' . $business->name)

@section('content')
<div class="p-4 sm:p-6 max-w-4xl mx-auto space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#25D366] to-[#128C7E] flex items-center justify-center shadow-lg shadow-emerald-500/30">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z"/></svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-white">WhatsApp Gateway</h1>
            <p class="text-sm text-slate-400">Hubungkan nomor WA bisnis Anda untuk kirim struk & promosi otomatis</p>
        </div>
    </div>

    {{-- SUCCESS / ERROR ALERTS --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div x-data="waGateway()" x-init="init()" class="space-y-6">

        {{-- ===== QR / STATUS CARD ===== --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm overflow-hidden">
            {{-- Card Header --}}
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-white">Status Koneksi</span>
                </div>
                <div>
                    <span x-show="status === 'connected'"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Terhubung
                    </span>
                    <span x-show="status === 'scan_qr'"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> Menunggu Scan
                    </span>
                    <span x-show="status === 'disconnected' || status === ''"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-700/50 text-slate-400 border border-slate-700">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span> Tidak Terhubung
                    </span>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="p-5">
                {{-- CONNECTED STATE --}}
                <div x-show="status === 'connected'" x-transition.opacity class="space-y-4">
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                        <div class="w-14 h-14 rounded-xl bg-[#25D366]/20 border border-[#25D366]/30 flex items-center justify-center shrink-0">
                            <svg class="w-8 h-8 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-medium mb-0.5">Nomor WhatsApp Bisnis</div>
                            <div class="text-lg font-extrabold text-white font-mono" x-text="phone ? '+' + phone : 'Sesi Aktif'"></div>
                            <div class="text-xs text-slate-500" x-text="deviceName ? 'Perangkat: ' + deviceName : ''"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('whatsapp.broadcast.create') }}"
                            class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-[#25D366]/15 hover:bg-[#25D366]/25 text-[#25D366] font-bold text-xs transition border border-[#25D366]/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            Blast Promosi
                        </a>
                        <button @click="disconnectWa()"
                            class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 font-bold text-xs transition border border-rose-500/20"
                            :disabled="isLoading">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Putus Koneksi
                        </button>
                    </div>
                </div>

                {{-- SCAN QR STATE --}}
                <div x-show="status === 'scan_qr'" x-transition.opacity class="text-center space-y-4">
                    <div class="text-sm text-slate-400 font-medium">Scan kode QR ini dengan WhatsApp di HP Anda</div>

                    {{-- QR Box --}}
                    <div class="flex justify-center">
                        <div class="relative inline-block">
                            <div x-show="!qrDataUrl" class="w-52 h-52 rounded-2xl bg-slate-800 border border-slate-700 flex flex-col items-center justify-center gap-3">
                                <div class="w-8 h-8 border-4 border-slate-600 border-t-[#25D366] rounded-full animate-spin"></div>
                                <span class="text-xs text-slate-500">Memuat QR Code...</span>
                            </div>
                            <div x-show="qrDataUrl" class="bg-white p-3 rounded-2xl shadow-2xl inline-block">
                                <img :src="qrDataUrl" alt="WhatsApp QR Code" class="w-48 h-48 rounded-xl block">
                            </div>
                        </div>
                    </div>

                    {{-- Steps --}}
                    <div class="text-left bg-slate-950/50 border border-slate-800 rounded-xl p-4 space-y-2 text-xs text-slate-400">
                        <p class="font-semibold text-slate-300 mb-2">Cara menghubungkan:</p>
                        <p>1. Buka <strong class="text-white">WhatsApp</strong> di HP Anda</p>
                        <p>2. Ketuk <strong class="text-white">⋮ Menu</strong> / <strong class="text-white">Pengaturan</strong> → <strong class="text-white">Perangkat Tertaut</strong></p>
                        <p>3. Ketuk <strong class="text-white">Tautkan Perangkat</strong> dan arahkan kamera ke kode QR</p>
                    </div>

                    <p class="text-[11px] text-emerald-400 font-semibold animate-pulse">⚡ Otomatis mendeteksi setelah scan...</p>
                </div>

                {{-- DISCONNECTED STATE --}}
                <div x-show="status === 'disconnected' || status === ''" x-transition.opacity class="text-center py-6 space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <p class="text-slate-300 font-semibold text-sm">WhatsApp Belum Terhubung</p>
                        <p class="text-slate-500 text-xs mt-1">Klik tombol di bawah untuk mulai scan QR Code</p>
                    </div>
                    <button @click="startSession()"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#25D366] hover:bg-[#22c55e] text-white font-bold text-sm transition shadow-lg shadow-[#25D366]/25"
                        :disabled="isLoading">
                        <svg x-show="isLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <svg x-show="!isLoading" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                        <span x-text="isLoading ? 'Menghubungkan...' : 'Mulai Scan QR Code'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== SETTINGS CARD ===== --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800">
                <h2 class="text-sm font-bold text-white">Pengaturan Struk Otomatis</h2>
                <p class="text-xs text-slate-500 mt-0.5">Struk digital akan dikirim otomatis ke WhatsApp pelanggan setiap transaksi POS</p>
            </div>
            <div class="p-5">
                <form action="{{ route('whatsapp.settings') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/50 border border-slate-800">
                        <div>
                            <p class="text-sm font-semibold text-white">Auto-Kirim Struk POS</p>
                            <p class="text-xs text-slate-500 mt-0.5">Kirim otomatis saat kasir checkout (jika pelanggan punya nomor HP)</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="auto_send_receipt" value="1" class="sr-only peer"
                                {{ ($waSession && $waSession->auto_send_receipt) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-[#25D366] peer-focus:ring-2 peer-focus:ring-[#25D366]/50 transition-colors after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Footer Struk Kustom (Opsional)</label>
                        <textarea name="receipt_template" rows="2" placeholder="Terima kasih! Kunjungi kami lagi 😊"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-200 focus:outline-none focus:border-[#25D366]/60 resize-none placeholder:text-slate-600">{{ $waSession?->receipt_template ?? '' }}</textarea>
                    </div>
                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 font-bold text-xs transition border border-emerald-500/30">
                        Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>

        {{-- ===== TEST SEND CARD ===== --}}
        <div x-show="status === 'connected'" x-transition.opacity
            class="rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800">
                <h2 class="text-sm font-bold text-white">Uji Coba Kirim Pesan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tes pengiriman pesan ke nomor HP manapun</p>
            </div>
            <div class="p-5 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1.5">Nomor Tujuan</label>
                        <input x-model="testPhone" type="tel" placeholder="08xxxxxxxxxx"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-[#25D366]/60 placeholder:text-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1.5">Pesan Tes</label>
                        <input x-model="testMessage" type="text" placeholder="Halo dari COOCA! 👋"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-[#25D366]/60 placeholder:text-slate-600">
                    </div>
                </div>
                <button @click="sendTest()"
                    class="w-full py-2.5 rounded-xl bg-[#25D366]/15 hover:bg-[#25D366]/25 text-[#25D366] font-bold text-xs transition border border-[#25D366]/30 flex items-center justify-center gap-2"
                    :disabled="testLoading">
                    <svg x-show="testLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="testLoading ? 'Mengirim...' : '📤 Kirim Tes'"></span>
                </button>
                <div x-show="testResult" x-text="testResult"
                    class="text-xs text-center font-semibold mt-1"
                    :class="testOk ? 'text-emerald-400' : 'text-rose-400'">
                </div>
            </div>
        </div>

        {{-- ===== QUICK LINKS ===== --}}
        <div class="grid grid-cols-3 gap-3">
            <a href="{{ route('whatsapp.broadcast.index') }}"
                class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-[#25D366]/40 hover:bg-[#25D366]/5 transition group text-center">
                <div class="w-10 h-10 rounded-xl bg-[#25D366]/10 flex items-center justify-center group-hover:bg-[#25D366]/20 transition">
                    <svg class="w-5 h-5 text-[#25D366]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <span class="text-xs font-semibold text-slate-300 group-hover:text-white transition">Blast Promosi</span>
            </a>
            <a href="{{ route('whatsapp.logs.index') }}"
                class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-slate-600 hover:bg-slate-800/50 transition group text-center">
                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center group-hover:bg-slate-700 transition">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="text-xs font-semibold text-slate-300 group-hover:text-white transition">Log Pesan</span>
            </a>
            <a href="{{ route('pos.terminal') }}"
                class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-teal-500/40 hover:bg-teal-500/5 transition group text-center">
                <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center group-hover:bg-teal-500/20 transition">
                    <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-xs font-semibold text-slate-300 group-hover:text-white transition">Terminal Kasir</span>
            </a>
        </div>

    </div>{{-- end x-data --}}
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
            this.pollTimer = setInterval(async () => {
                try {
                    const res = await fetch('{{ route('whatsapp.qr') }}', {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                    });
                    const data = await res.json();

                    const raw = (data.status || '').toUpperCase();
                    if (raw === 'CONNECTED') {
                        this.status = 'connected';
                        clearInterval(this.pollTimer);
                        // Refresh page to load full connected state
                        setTimeout(() => window.location.reload(), 1200);
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
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
            if (!confirm('Yakin ingin memutus koneksi WhatsApp?')) return;
            this.isLoading = true;
            try {
                await fetch('{{ route('whatsapp.disconnect') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });
                this.status = 'disconnected';
                this.phone = '';
                this.deviceName = '';
            } catch (e) {} finally {
                this.isLoading = false;
            }
        },

        async sendTest() {
            if (!this.testPhone.trim() || !this.testMessage.trim()) {
                this.testResult = '⚠️ Isi nomor dan pesan terlebih dahulu.';
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ phone: this.testPhone, message: this.testMessage })
                });
                const data = await res.json();
                this.testOk = data.success ?? false;
                this.testResult = this.testOk ? '✅ Pesan berhasil dikirim!' : ('❌ Gagal: ' + (data.error || 'Unknown error'));
            } catch (e) {
                this.testResult = '❌ Error jaringan';
                this.testOk = false;
            } finally {
                this.testLoading = false;
            }
        }
    };
}
</script>
@endpush
