@extends('layouts.admin', [
    'title' => 'Detail Workspace: ' . $business->name . ' — Admin Console',
    'headerTitle' => 'Workspace: ' . $business->name,
    'headerSubtitle' => 'Detail lengkap profil tenant, kuota sumber daya, pengguna, dan status langganan'
])

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Actions Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route('admin.businesses.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Bisnis</span>
        </a>

        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.businesses.toggle-status', $business->id) }}"
                  onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin mengubah status aktif bisnis ini?', 'Ubah Status Bisnis?', 'warning')">
                @csrf
                @if($business->is_active)
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 text-xs font-bold flex items-center gap-1.5 transition">
                        <i data-lucide="ban" class="w-4 h-4"></i>
                        <span>Tangguhkan Workspace (Suspend)</span>
                    </button>
                @else
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-xs font-bold flex items-center gap-1.5 transition">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Aktifkan Kembali Workspace</span>
                    </button>
                @endif
            </form>
        </div>
    </div>

    <!-- Workspace Identity Card -->
    <div class="glass-card p-6 rounded-3xl border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-2 z-10">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $business->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                    {{ $business->is_active ? 'Status: Aktif' : 'Status: Ditangguhkan' }}
                </span>

                @if($business->subscription?->isCorePlan())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        PAKET CORE ({{ strtoupper($business->subscription->plan_code) }})
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-800 text-slate-400 border border-slate-700">
                        FREE PLAN
                    </span>
                @endif

                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                    {{ $isSoloMode ? 'Mode Solo Owner (1 User)' : 'Mode Tim Delegasi' }}
                </span>
            </div>

            <h2 class="text-2xl font-black text-white tracking-tight">
                {{ $business->name }}
            </h2>

            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400 font-mono">
                <span>Slug: {{ $business->slug }}</span>
                <span>&bull;</span>
                <span>Mata Uang: {{ $business->currency ?? 'IDR' }} ({{ $business->currency_symbol }})</span>
                <span>&bull;</span>
                <span>Dibuat: {{ $business->created_at?->format('d M Y') }}</span>
            </div>

            @if(!$business->is_active && $business->suspended_reason)
                <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-300 mt-2">
                    <strong>Alasan Penangguhan:</strong> {{ $business->suspended_reason }} (sejak {{ $business->suspended_at?->format('d M Y H:i') }})
                </div>
            @endif
        </div>
    </div>

    <!-- 4 Resources KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="glass-card p-5 rounded-2xl border-slate-800">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Total Produk</div>
            <div class="text-3xl font-black text-white font-mono">{{ $productCount }}</div>
            <div class="text-[10px] text-slate-500 mt-1">Katalog item toko</div>
        </div>

        <div class="glass-card p-5 rounded-2xl border-slate-800">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Resep / BOM</div>
            <div class="text-3xl font-black text-emerald-400 font-mono">{{ $bomCount }}</div>
            <div class="text-[10px] text-slate-500 mt-1">Struktur biaya HPP</div>
        </div>

        <div class="glass-card p-5 rounded-2xl border-slate-800">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Transaksi Toko</div>
            <div class="text-3xl font-black text-blue-400 font-mono">{{ $invoiceCount + $posOrderCount }}</div>
            <div class="text-[10px] text-slate-500 mt-1">{{ $posOrderCount }} POS &bull; {{ $invoiceCount }} Faktur</div>
        </div>

        <div class="glass-card p-5 rounded-2xl border-slate-800">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Token AI Terpakai</div>
            <div class="text-3xl font-black text-purple-400 font-mono">{{ number_format($totalAiTokensUsed, 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-500 mt-1">Konsumsi Google Gemini</div>
        </div>
    </div>

    <!-- Two-Column Layout (Details & Team) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Col: Profile & Subscriptions (7 cols) -->
        <div class="space-y-6 lg:col-span-7">
            
            <!-- Commercial Profile -->
            <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i data-lucide="store" class="w-4 h-4 text-blue-400"></i>
                    <span>Informasi Komersial & Finansial</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <div class="text-slate-500">Email Bisnis</div>
                        <div class="text-white font-medium mt-0.5">{{ $business->email ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Nomor Telepon</div>
                        <div class="text-white font-medium mt-0.5">{{ $business->phone ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Bank & No Rekening</div>
                        <div class="text-white font-mono mt-0.5">
                            {{ $business->bank_name ? $business->bank_name . ' - ' . $business->bank_account_number : '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-slate-500">NPWP</div>
                        <div class="text-white font-mono mt-0.5">{{ $business->tax_identification_number ?? '-' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="text-slate-500">Alamat Fisik</div>
                        <div class="text-slate-300 mt-0.5">{{ $business->address ?? 'Belum dicantumkan' }}</div>
                    </div>
                </div>
            </div>

            <!-- Subscription Status Card -->
            <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                        <span>Status Langganan SaaS</span>
                    </h3>
                    <span class="text-xs font-mono text-slate-400">
                        Sisa Token AI: <strong class="text-purple-400">{{ number_format($business->subscription?->ai_tokens_remaining ?? 0) }}</strong>
                    </span>
                </div>

                @if($business->subscription)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <div class="text-slate-500">Paket</div>
                        <div class="font-bold text-white mt-0.5 uppercase">{{ $business->subscription->plan_code }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Mulai Aktif</div>
                        <div class="text-slate-300 mt-0.5">{{ $business->subscription->starts_at?->format('d M Y') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Berakhir Pada</div>
                        <div class="text-emerald-400 font-bold mt-0.5">{{ $business->subscription->ends_at?->format('d M Y') ?? 'Tanpa Batas' }}</div>
                    </div>
                </div>
                @else
                <div class="text-xs text-slate-400">
                    Bisnis ini saat ini berada pada <strong>Free Plan</strong> (kuota gratis bawaan).
                </div>
                @endif
            </div>

            <!-- Recent Subscription Payments -->
            <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i data-lucide="receipt" class="w-4 h-4 text-amber-400"></i>
                    <span>Riwayat Tagihan & Pembayaran Langganan</span>
                </h3>

                @if($business->subscriptionPayments->isEmpty())
                <div class="text-xs text-slate-500 py-4 text-center">
                    Belum ada riwayat transaksi pembayaran paket.
                </div>
                @else
                <div class="space-y-2 text-xs">
                    @foreach($business->subscriptionPayments as $pay)
                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-white font-mono">{{ $pay->order_number }}</div>
                            <div class="text-[10px] text-slate-400">
                                {{ $pay->plan_code }} &bull; {{ $pay->created_at?->format('d M Y H:i') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono font-bold text-white">
                                Rp {{ number_format((float) $pay->total_amount, 0, ',', '.') }}
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase {{ $pay->status === 'approved' ? 'bg-emerald-500/20 text-emerald-300' : ($pay->status === 'awaiting_approval' ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-800 text-slate-400') }}">
                                {{ $pay->status }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Right Col: Users & Team (5 cols) -->
        <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-4 lg:col-span-5 h-fit">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-cyan-400"></i>
                    <span>Pengguna Terdaftar ({{ $business->users->count() }})</span>
                </h3>
            </div>

            <div class="space-y-3">
                @foreach($business->users as $u)
                <div class="p-3 rounded-2xl bg-slate-900/60 border border-slate-800 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-white">{{ $u->name }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">{{ $u->email }}</div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ ($u->pivot->role ?? 'member') === 'owner' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-slate-800 text-slate-300' }}">
                            {{ $u->pivot->role ?? 'Staf' }}
                        </span>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            {{ $u->google_id ? 'Google' : 'Password' }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
