@extends('layouts.admin')

@section('title', 'WhatsApp Center — Pengingat Langganan & Blast Bisnis Owner')

@section('content')
<div class="space-y-6" x-data="adminWaCenter()" x-init="init()">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#25D366] to-[#128C7E] flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-white">WhatsApp Center Administrator</h1>
                    <p class="text-xs text-slate-400 mt-0.5">Otomasi pengingat masa patungan/langganan (H-7 s/d Hari H) & blast promosi ke seluruh bisnis owner</p>
                </div>
            </div>
        </div>

        {{-- STATUS BADGE & QUICK RECONNECT --}}
        <div class="flex items-center gap-2">
            <span x-show="status === 'connected'" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Bot Terhubung (<span x-text="phone ? '+' + phone : 'Aktif'"></span>)
            </span>
            <span x-show="status !== 'connected'" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> Bot Belum Terhubung
            </span>
            <button @click="activeTab = 'connection'" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition">
                Kelola Koneksi
            </button>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- NAVIGATION TABS --}}
    <div class="flex items-center gap-2 border-b border-slate-800 pb-3 overflow-x-auto text-xs font-bold">
        <button @click="activeTab = 'reminders'"
            :class="activeTab === 'reminders' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800'"
            class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 shrink-0">
            <i data-lucide="bell-ring" class="w-4 h-4"></i>
            <span>Pengingat Langganan (H-7 s/d Hari H)</span>
            @if($dueData['stats']['total_pending'] > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-amber-500 text-slate-950 text-[10px] font-black">
                    {{ $dueData['stats']['total_pending'] }}
                </span>
            @endif
        </button>

        <button @click="activeTab = 'blast'"
            :class="activeTab === 'blast' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800'"
            class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 shrink-0">
            <i data-lucide="send" class="w-4 h-4"></i>
            <span>Blast ke Bisnis Owner</span>
            <span class="px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">Teks + Gambar</span>
        </button>

        <button @click="activeTab = 'templates'"
            :class="activeTab === 'templates' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800'"
            class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 shrink-0">
            <i data-lucide="file-code-2" class="w-4 h-4"></i>
            <span>Template Pesan Otomatis</span>
        </button>

        <button @click="activeTab = 'connection'"
            :class="activeTab === 'connection' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800'"
            class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 shrink-0">
            <i data-lucide="qr-code" class="w-4 h-4"></i>
            <span>Scan QR / Koneksi Bot</span>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: PENGINGAT LANGGANAN (H-7, H-3, H-1, HARI H) --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'reminders'" class="space-y-6">

        {{-- SUMMARY STATS & TRIGGER ALL --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="p-4 rounded-2xl glass-card text-center">
                <div class="text-2xl font-black text-white">{{ $dueData['stats']['total_due'] }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Total Bisnis Jatuh Tempo</div>
            </div>
            <div class="p-4 rounded-2xl glass-card text-center">
                <div class="text-2xl font-black text-amber-400">{{ $dueData['stats']['total_pending'] }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Menunggu Pengingat Hari Ini</div>
            </div>
            <div class="p-4 rounded-2xl glass-card text-center">
                <div class="text-2xl font-black text-emerald-400">{{ $dueData['stats']['total_sent'] }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Sudah Dikirim Hari Ini</div>
            </div>
            <div class="p-4 rounded-2xl glass-card flex flex-col justify-center items-center">
                <form action="{{ route('admin.whatsapp.reminders.send-all') }}" method="POST" onsubmit="return confirm('Kirim notifikasi pengingat ke SEMUA bisnis owner yang jatuh tempo hari ini?')">
                    @csrf
                    <button type="submit" :disabled="status !== 'connected' || {{ $dueData['stats']['total_pending'] }} === 0"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] hover:from-[#22c55e] hover:to-[#0f7a6a] text-white font-extrabold text-xs shadow-lg shadow-emerald-500/20 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1.5">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        <span>Kirim Semua Hari Ini</span>
                    </button>
                </form>
                <div class="text-[10px] text-slate-500 mt-1">Otomatis terjadwal via cron setiap hari</div>
            </div>
        </div>

        {{-- INTERVAL ACCORDIONS: H-7, H-3, H-1, HARI H --}}
        @php
            $intervalConfigs = [
                'h-7'    => ['title' => 'H-7: Berakhir dalam 7 Hari', 'badge' => 'bg-blue-500/10 text-blue-400 border-blue-500/30', 'icon' => 'calendar-clock'],
                'h-3'    => ['title' => 'H-3: Berakhir dalam 3 Hari', 'badge' => 'bg-amber-500/10 text-amber-400 border-amber-500/30', 'icon' => 'clock'],
                'h-1'    => ['title' => 'H-1: Berakhir Besok', 'badge' => 'bg-rose-500/10 text-rose-400 border-rose-500/30', 'icon' => 'alert-triangle'],
                'hari_h' => ['title' => 'Hari H: Berakhir Hari Ini (Expired)', 'badge' => 'bg-red-500/20 text-red-300 border-red-500/40', 'icon' => 'alert-octagon'],
            ];
        @endphp

        <div class="space-y-4">
            @foreach($intervalConfigs as $key => $conf)
                @php $items = $dueData[$key]; @endphp
                <div class="rounded-2xl glass-card overflow-hidden border border-slate-800">
                    <div class="px-5 py-3.5 border-b border-slate-800/80 flex items-center justify-between bg-slate-950/40">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $conf['badge'] }}">
                                {{ $conf['title'] }}
                            </span>
                            <span class="text-xs text-slate-400">({{ count($items) }} bisnis)</span>
                        </div>
                    </div>

                    @if(empty($items))
                        <div class="p-6 text-center text-xs text-slate-500">
                            Tidak ada langganan yang jatuh tempo pada interval ini.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-500 uppercase tracking-wide">
                                        <th class="text-left px-5 py-3 font-semibold">Nama Bisnis & Owner</th>
                                        <th class="text-left px-3 py-3 font-semibold">No. WhatsApp</th>
                                        <th class="text-left px-3 py-3 font-semibold">Paket</th>
                                        <th class="text-left px-3 py-3 font-semibold">Tanggal Berakhir</th>
                                        <th class="text-center px-3 py-3 font-semibold">Status Notif Hari Ini</th>
                                        <th class="text-right px-5 py-3 font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60">
                                    @foreach($items as $row)
                                        <tr class="hover:bg-slate-800/30 transition">
                                            <td class="px-5 py-3.5">
                                                <div class="font-bold text-white text-xs">{{ $row['business']->name }}</div>
                                                <div class="text-slate-400 text-[11px]">{{ $row['owner']?->name ?? 'Owner' }} ({{ $row['owner']?->email ?? '-' }})</div>
                                            </td>
                                            <td class="px-3 py-3.5 font-mono text-slate-300">
                                                {{ $row['phone'] ?: 'Belum diisi' }}
                                            </td>
                                            <td class="px-3 py-3.5">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">
                                                    {{ $row['plan_name'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-3.5 text-slate-300 font-semibold">
                                                {{ $row['ends_at'] ? $row['ends_at']->format('d M Y') : '-' }}
                                            </td>
                                            <td class="px-3 py-3.5 text-center">
                                                @if($row['already_sent'])
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                                        ✓ Terkirim Hari Ini
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                                        ⏳ Belum Dikirim
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <button @click="sendSingleReminder('{{ $row['subscription']->id }}', '{{ $key }}')"
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-300 text-[11px] font-bold border border-emerald-500/30 transition flex items-center gap-1 ml-auto">
                                                    <i data-lucide="send" class="w-3 h-3"></i>
                                                    <span>Kirim WA</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- RIWAYAT PENGINGAT TERAKHIR --}}
        <div class="rounded-2xl glass-card overflow-hidden border border-slate-800">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-sm font-bold text-white">Riwayat Pengingat WhatsApp Terkirim</h2>
            </div>
            @if($recentReminders->isEmpty())
                <div class="p-8 text-center text-xs text-slate-500">Belum ada pengingat yang tercatat.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-500 uppercase tracking-wide">
                                <th class="text-left px-5 py-3 font-semibold">Tipe</th>
                                <th class="text-left px-3 py-3 font-semibold">Bisnis & Owner</th>
                                <th class="text-left px-3 py-3 font-semibold">No. WA</th>
                                <th class="text-left px-3 py-3 font-semibold">Pesan</th>
                                <th class="text-center px-3 py-3 font-semibold">Status</th>
                                <th class="text-left px-3 py-3 font-semibold">Waktu Kirim</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach($recentReminders as $reminder)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="px-5 py-3 font-bold uppercase text-[10px] text-slate-300">
                                        {{ $reminder->reminder_type }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-semibold text-white">{{ $reminder->business_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $reminder->owner_name }}</div>
                                    </td>
                                    <td class="px-3 py-3 font-mono text-slate-300">{{ $reminder->recipient_phone }}</td>
                                    <td class="px-3 py-3 text-slate-400 truncate max-w-xs" title="{{ $reminder->message }}">
                                        {{ Str::limit($reminder->message, 50) }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @if($reminder->status === 'sent')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Terkirim</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Gagal</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-slate-500">{{ $reminder->sent_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-800">
                    {{ $recentReminders->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: BLAST KE SELURUH BISNIS OWNER --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'blast'" class="space-y-6">

        {{-- AUDIENCE METRICS CARDS --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-4 rounded-2xl glass-card text-center">
                <div class="text-2xl font-black text-white">{{ number_format($targetCounts['all_owners']) }}</div>
                <div class="text-xs text-slate-400 mt-0.5">Semua Bisnis Owner</div>
            </div>
            <div class="p-4 rounded-2xl glass-card text-center">
                <div class="text-2xl font-black text-emerald-400">{{ number_format($targetCounts['active_subscribers']) }}</div>
                <div class="text-xs text-slate-400 mt-0.5">Langganan Aktif (Core)</div>
            </div>
            <div class="p-4 rounded-2xl glass-card text-center">
                <div class="text-2xl font-black text-amber-400">{{ number_format($targetCounts['expiring_soon']) }}</div>
                <div class="text-xs text-slate-400 mt-0.5">Segera Habis (≤ 7 Hari)</div>
            </div>
            <div class="p-4 rounded-2xl glass-card text-center">
                <div class="text-2xl font-black text-slate-400">{{ number_format($targetCounts['free_tier']) }}</div>
                <div class="text-xs text-slate-400 mt-0.5">Paket Free / Non-Aktif</div>
            </div>
        </div>

        {{-- FORM BLAST & LIVE PREVIEW --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Form (3 Cols) --}}
            <div class="lg:col-span-3 space-y-4">
                <form action="{{ route('admin.whatsapp.blasts.store') }}" method="POST" id="adminBlastForm" @submit.prevent="submitBlast">
                    @csrf

                    <div class="rounded-2xl glass-card p-5 space-y-4 border border-slate-800">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-1.5">Judul Kampanye (Internal)</label>
                            <input type="text" name="title" x-model="blastTitle" required
                                placeholder="Pengumuman Fitur Baru / Promo Perpanjangan / Maintenance Server"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-1.5">Pilih Target Bisnis Owner</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="target_filter" value="all_owners" x-model="blastTarget" class="sr-only">
                                    <div class="p-3 rounded-xl border text-center transition"
                                        :class="blastTarget === 'all_owners' ? 'border-[#25D366] bg-[#25D366]/10 text-[#25D366]' : 'border-slate-800 bg-slate-950/60 text-slate-400'">
                                        <div class="font-bold text-xs">Semua Owner</div>
                                        <div class="text-[10px] opacity-70 mt-0.5">{{ $targetCounts['all_owners'] }} Bisnis</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="target_filter" value="active_subscribers" x-model="blastTarget" class="sr-only">
                                    <div class="p-3 rounded-xl border text-center transition"
                                        :class="blastTarget === 'active_subscribers' ? 'border-[#25D366] bg-[#25D366]/10 text-[#25D366]' : 'border-slate-800 bg-slate-950/60 text-slate-400'">
                                        <div class="font-bold text-xs">Langganan Aktif</div>
                                        <div class="text-[10px] opacity-70 mt-0.5">{{ $targetCounts['active_subscribers'] }} Bisnis</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="target_filter" value="expiring_soon" x-model="blastTarget" class="sr-only">
                                    <div class="p-3 rounded-xl border text-center transition"
                                        :class="blastTarget === 'expiring_soon' ? 'border-[#25D366] bg-[#25D366]/10 text-[#25D366]' : 'border-slate-800 bg-slate-950/60 text-slate-400'">
                                        <div class="font-bold text-xs">Segera Habis (≤7 Hari)</div>
                                        <div class="text-[10px] opacity-70 mt-0.5">{{ $targetCounts['expiring_soon'] }} Bisnis</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="target_filter" value="free_tier" x-model="blastTarget" class="sr-only">
                                    <div class="p-3 rounded-xl border text-center transition"
                                        :class="blastTarget === 'free_tier' ? 'border-[#25D366] bg-[#25D366]/10 text-[#25D366]' : 'border-slate-800 bg-slate-950/60 text-slate-400'">
                                        <div class="font-bold text-xs">Paket Free</div>
                                        <div class="text-[10px] opacity-70 mt-0.5">{{ $targetCounts['free_tier'] }} Bisnis</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-1.5">URL Banner Gambar (Opsional)</label>
                            <input type="url" name="media_url" x-model="blastMediaUrl" @input="updatePreview()"
                                placeholder="https://cooca.id/images/promo-banner.jpg"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wide">Pesan Blast</label>
                                <div class="flex gap-1">
                                    @foreach(['{owner}', '{bisnis}', '{paket}', '{tanggal_habis}'] as $v)
                                        <button type="button" @click="insertBlastVar('{{ $v }}')" class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 hover:text-white text-[10px] font-mono border border-slate-700">
                                            {{ $v }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <textarea name="message" id="adminBlastTextarea" x-model="blastMessage" rows="7" required
                                placeholder="Halo {owner} ({bisnis}) 👋&#10;Kami punya pengumuman penting mengenai update sistem Cooca POS..."
                                @input="updatePreview()"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white font-mono focus:outline-none focus:border-[#25D366] resize-none"></textarea>
                            <div class="text-[10px] text-slate-500 text-right mt-1" x-text="blastMessage.length + ' karakter'"></div>
                        </div>

                        <button type="submit" :disabled="submitting || !blastMessage.trim() || !blastTitle.trim() || status !== 'connected'"
                            class="w-full py-3 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] hover:from-[#22c55e] hover:to-[#0f7a6a] text-white font-bold text-xs shadow-lg shadow-emerald-500/20 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span x-text="submitting ? 'Mengirim Blast ke Seluruh Owner...' : 'Kirim Blast ke Bisnis Owner Terpilih'"></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Live Smartphone Mockup (2 Cols) --}}
            <div class="lg:col-span-2">
                <div class="sticky top-6">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Live Preview (Tampilan WhatsApp Owner)</div>
                    <div class="rounded-[2.5rem] bg-slate-950 border-4 border-slate-700 shadow-2xl overflow-hidden mx-auto max-w-[280px]" style="height: 480px;">
                        <div class="bg-[#075E54] px-4 pt-10 pb-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#25D366]/30 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white">COOCA Official</div>
                                <div class="text-[9px] text-[#25D366]">Akun Resmi Platform</div>
                            </div>
                        </div>
                        <div class="bg-[#E5DDD5] h-full overflow-y-auto px-3 pt-3 pb-12">
                            <div class="flex justify-end mb-2">
                                <div class="max-w-[85%] rounded-2xl rounded-tr-sm bg-[#DCF8C6] px-3 py-2 shadow-sm">
                                    <div x-show="blastMediaUrl" class="mb-2 rounded-xl overflow-hidden">
                                        <img :src="blastMediaUrl" alt="banner" class="w-full max-h-28 object-cover rounded-xl" onerror="this.style.display='none'">
                                    </div>
                                    <p class="text-[11px] text-[#303030] whitespace-pre-wrap break-words"
                                        x-text="blastPreview || 'Ketik pesan di form untuk melihat preview...'"></p>
                                    <div class="text-[9px] text-[#8B8B8B] text-right mt-1 flex items-center justify-end gap-1">
                                        <span>{{ now()->format('H:i') }}</span>
                                        <svg class="w-3 h-3 text-[#4FC3F7]" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIWAYAT BLAST DARI ADMIN --}}
        <div class="rounded-2xl glass-card overflow-hidden border border-slate-800">
            <div class="px-5 py-4 border-b border-slate-800">
                <h2 class="text-sm font-bold text-white">Riwayat Blast Administrator</h2>
            </div>
            @if($blasts->isEmpty())
                <div class="p-8 text-center text-xs text-slate-500">Belum ada blast yang dikirim oleh admin.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-500 uppercase tracking-wide">
                                <th class="text-left px-5 py-3 font-semibold">Judul Kampanye</th>
                                <th class="text-left px-3 py-3 font-semibold">Target</th>
                                <th class="text-center px-3 py-3 font-semibold">Total Target</th>
                                <th class="text-center px-3 py-3 font-semibold">Terkirim</th>
                                <th class="text-center px-3 py-3 font-semibold">Status</th>
                                <th class="text-left px-3 py-3 font-semibold">Tanggal</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach($blasts as $blast)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-white">{{ $blast->title }}</div>
                                        <div class="text-slate-500 text-[10px] truncate max-w-xs">{{ Str::limit($blast->message, 50) }}</div>
                                    </td>
                                    <td class="px-3 py-3.5 text-slate-300 font-medium">
                                        {{ str_replace('_', ' ', ucfirst($blast->target_filter)) }}
                                    </td>
                                    <td class="px-3 py-3.5 text-center font-bold text-white">{{ $blast->total_recipients }}</td>
                                    <td class="px-3 py-3.5 text-center font-bold text-emerald-400">
                                        {{ $blast->total_sent }}
                                        @if($blast->total_failed > 0)
                                            <span class="text-rose-400 font-normal">({{ $blast->total_failed }} gagal)</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3.5 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $blast->status === 'completed' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20' }}">
                                            {{ ucfirst($blast->status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 text-slate-500">{{ $blast->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <a href="{{ route('admin.whatsapp.blasts.show', $blast) }}" class="text-xs font-semibold text-indigo-400 hover:underline">Detail Penerima →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 3: TEMPLATE PESAN OTOMATIS (H-7, H-3, H-1, HARI H) --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'templates'" class="space-y-6">
        <div class="rounded-2xl glass-card p-6 border border-slate-800">
            <h2 class="text-sm font-bold text-white mb-2">Sesuaikan Template Pengingat Masa Patungan/Langganan</h2>
            <p class="text-xs text-slate-400 mb-6">Pesan ini dikirim otomatis via WhatsApp Bot ke nomor owner ketika masa langganan mencapai H-7, H-3, H-1, atau Hari H.</p>

            <div class="p-3 mb-6 rounded-xl bg-slate-950/60 border border-slate-800 text-xs text-slate-300 flex flex-wrap gap-4 items-center">
                <span class="font-bold text-indigo-400">Variabel yang Tersedia:</span>
                <code>{owner}</code> <span class="text-slate-500">= Nama Pemilik</span>
                <code>{bisnis}</code> <span class="text-slate-500">= Nama Usaha</span>
                <code>{paket}</code> <span class="text-slate-500">= Nama Paket Langganan</span>
                <code>{tanggal_habis}</code> <span class="text-slate-500">= Tanggal Jatuh Tempo</span>
                <code>{link_bayar}</code> <span class="text-slate-500">= Link Halaman Tagihan</span>
            </div>

            <form action="{{ route('admin.whatsapp.reminders.templates') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-blue-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-400"></span> Template H-7 (7 Hari Sebelum Berakhir)
                    </label>
                    <textarea name="template_h7" rows="5" required
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white font-mono focus:outline-none focus:border-blue-500 resize-none">{{ $templates['h-7'] }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-amber-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span> Template H-3 (3 Hari Sebelum Berakhir)
                    </label>
                    <textarea name="template_h3" rows="5" required
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white font-mono focus:outline-none focus:border-amber-500 resize-none">{{ $templates['h-3'] }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-rose-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span> Template H-1 (1 Hari Sebelum Berakhir — Besok)
                    </label>
                    <textarea name="template_h1" rows="5" required
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white font-mono focus:outline-none focus:border-rose-500 resize-none">{{ $templates['h-1'] }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-red-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-400"></span> Template Hari H (Jatuh Tempo Hari Ini / Expired)
                    </label>
                    <textarea name="template_h0" rows="5" required
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white font-mono focus:outline-none focus:border-red-500 resize-none">{{ $templates['hari_h'] }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition shadow-lg shadow-indigo-600/30">
                        Simpan Semua Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 4: KONEKSI SCAN QR BOT WHATSAPP ADMIN --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'connection'" class="space-y-6">
        <div class="max-w-2xl mx-auto rounded-2xl glass-card p-6 border border-slate-800 space-y-6">
            <div class="text-center space-y-2">
                <h2 class="text-base font-extrabold text-white">Koneksi WhatsApp Resmi Administrator</h2>
                <p class="text-xs text-slate-400">Nomor ini digunakan untuk mengirim struk tagihan, pengingat masa patungan, dan pengumuman platform.</p>
            </div>

            {{-- CONNECTED CARD --}}
            <div x-show="status === 'connected'" class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#25D366]/20 border border-[#25D366]/40 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-emerald-400 font-bold uppercase tracking-wider">Perangkat Resmi Terhubung</div>
                        <div class="text-lg font-black text-white font-mono" x-text="phone ? '+' + phone : 'WhatsApp Bot Aktif'"></div>
                        <div class="text-[11px] text-slate-400" x-text="deviceName ? 'Perangkat: ' + deviceName : ''"></div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button @click="disconnectBot()" class="px-4 py-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 font-bold text-xs border border-rose-500/30 transition">
                        Putus Koneksi
                    </button>
                </div>
            </div>

            {{-- SCAN QR CARD --}}
            <div x-show="status === 'scan_qr'" class="text-center space-y-4">
                <div class="text-xs text-slate-400">Scan QR Code ini menggunakan nomor resmi Cooca (<strong>0823 3749 9577</strong>)</div>
                <div class="flex justify-center">
                    <div class="bg-white p-3 rounded-2xl shadow-2xl inline-block">
                        <img :src="qrDataUrl" alt="QR Code" class="w-48 h-48 rounded-xl block">
                    </div>
                </div>
                <div class="text-xs text-emerald-400 font-semibold animate-pulse">Menunggu pemindaian barcode...</div>
            </div>

            {{-- DISCONNECTED / START BUTTON --}}
            <div x-show="status === 'disconnected' || status === ''" class="text-center py-6 space-y-3">
                <div class="text-xs text-slate-400">WhatsApp Admin sedang tidak terhubung.</div>
                <button @click="startBot()" :disabled="isLoading"
                    class="px-6 py-3 rounded-xl bg-[#25D366] hover:bg-[#22c55e] text-white font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-2 mx-auto">
                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                    <span x-text="isLoading ? 'Memuat QR Code...' : 'Tampilkan QR Code untuk Scan'"></span>
                </button>
            </div>

            {{-- TEST SEND PANEL --}}
            <div x-show="status === 'connected'" class="pt-4 border-t border-slate-800 space-y-3">
                <div class="text-xs font-bold text-white uppercase tracking-wider">Uji Coba Kirim Pesan</div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="tel" x-model="testPhone" placeholder="08xxxxxxxxxx"
                        class="bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <input type="text" x-model="testMsg" placeholder="Pesan tes..."
                        class="bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>
                <button @click="sendTestMsg()" :disabled="testLoading"
                    class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs border border-slate-700 transition">
                    <span x-text="testLoading ? 'Mengirim...' : 'Kirim Pesan Tes'"></span>
                </button>
                <div x-show="testResult" x-text="testResult" class="text-center text-xs font-semibold"
                    :class="testOk ? 'text-emerald-400' : 'text-rose-400'"></div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function adminWaCenter() {
    return {
        activeTab: '{{ $tab }}',
        status: '{{ $waStatus['status'] ?? 'disconnected' }}',
        phone: '{{ isset($waStatus['user']['id']) ? explode(':', $waStatus['user']['id'])[0] : '' }}',
        deviceName: '{{ $waStatus['user']['name'] ?? '' }}',
        qrDataUrl: null,
        isLoading: false,
        pollTimer: null,

        // Blast form
        blastTitle: '',
        blastTarget: 'all_owners',
        blastMediaUrl: '',
        blastMessage: `Halo {owner} ({bisnis}) 👋\n\nKami punya pengumuman penting untuk bisnis Anda...`,
        blastPreview: '',
        submitting: false,

        // Test
        testPhone: '082337499577',
        testMsg: 'Halo dari Admin Cooca Platform! 👋 Notifikasi WhatsApp siap digunakan.',
        testLoading: false,
        testResult: '',
        testOk: false,

        init() {
            this.updatePreview();
            if (this.status !== 'connected') {
                this.pollQr();
            }
        },

        insertBlastVar(v) {
            const ta = document.getElementById('adminBlastTextarea');
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            this.blastMessage = this.blastMessage.substring(0, start) + v + this.blastMessage.substring(end);
            this.$nextTick(() => {
                ta.selectionStart = ta.selectionEnd = start + v.length;
                ta.focus();
                this.updatePreview();
            });
        },

        updatePreview() {
            this.blastPreview = this.blastMessage
                .replace(/\{owner\}/g, 'Budi Pratama')
                .replace(/\{bisnis\}/g, 'Bengkel Motor Jaya')
                .replace(/\{paket\}/g, 'Cooca Core')
                .replace(/\{tanggal_habis\}/g, '15/09/2026');
        },

        pollQr() {
            this.pollTimer = setInterval(async () => {
                try {
                    const res = await fetch("{{ route('admin.whatsapp.qr') }}");
                    const data = await res.json();
                    const raw = (data.status || '').toUpperCase();
                    if (raw === 'CONNECTED') {
                        this.status = 'connected';
                        clearInterval(this.pollTimer);
                        setTimeout(() => window.location.reload(), 1200);
                    } else if (raw === 'SCAN_QR') {
                        this.status = 'scan_qr';
                        this.qrDataUrl = data.qrDataUrl || null;
                    }
                } catch (e) {}
            }, 3000);
        },

        async startBot() {
            this.isLoading = true;
            try {
                const res = await fetch("{{ route('admin.whatsapp.start') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await res.json();
                this.status = 'scan_qr';
                this.pollQr();
            } catch (e) {} finally {
                this.isLoading = false;
            }
        },

        async disconnectBot() {
            if (!confirm('Putus koneksi WhatsApp Admin?')) return;
            try {
                await fetch("{{ route('admin.whatsapp.disconnect') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                this.status = 'disconnected';
                this.phone = '';
            } catch (e) {}
        },

        async sendSingleReminder(subId, type) {
            if (!confirm(`Kirim pengingat ${type} ke nomor owner bisnis ini sekarang?`)) return;
            try {
                const res = await fetch(`/admin/whatsapp/reminders/${subId}/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ type: type })
                });
                const data = await res.json();
                alert(data.message);
                window.location.reload();
            } catch (e) {
                alert('Gagal mengirim pengingat.');
            }
        },

        async submitBlast() {
            if (this.submitting) return;
            if (!confirm('Yakin ingin mengirim pesan broadcast ini ke seluruh bisnis owner terpilih?')) return;
            this.submitting = true;
            document.getElementById('adminBlastForm').submit();
        },

        async sendTestMsg() {
            this.testLoading = true;
            this.testResult = '';
            try {
                const res = await fetch("{{ route('admin.whatsapp.test') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone: this.testPhone, message: this.testMsg })
                });
                const data = await res.json();
                this.testOk = data.success ?? false;
                this.testResult = this.testOk ? '✓ Pesan tes berhasil terkirim!' : ('✗ Gagal: ' + (data.error || 'Unknown'));
            } catch (e) {
                this.testResult = '✗ Error jaringan';
                this.testOk = false;
            } finally {
                this.testLoading = false;
            }
        }
    };
}
</script>
@endpush
