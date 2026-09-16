@extends('layouts.public_marketing', [
    'title' => 'Status Pemulihan Akses Akun - Cooca',
    'noindex' => true,
])

@section('content')
    <div class="relative min-h-[calc(100vh-5rem)] flex items-center justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <!-- Ambient Apple Background Glow -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none -z-10">
            <div
                class="absolute -top-32 left-1/2 -translate-x-1/2 w-[640px] h-[340px] bg-gradient-to-tr from-[#007AFF]/15 to-[#5856D6]/10 dark:from-[#0A84FF]/10 dark:to-[#5E5CE6]/10 rounded-full blur-[100px] opacity-75">
            </div>
            <div
                class="absolute bottom-10 left-1/2 -translate-x-1/3 w-[500px] h-[300px] bg-[#34C759]/10 dark:bg-[#30D158]/5 rounded-full blur-[90px] opacity-50">
            </div>
        </div>

        <div class="w-full max-w-xl mx-auto space-y-6">
            <!-- Header -->
            <div class="text-center space-y-2 mb-2">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-[20px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] shadow-sm mb-2">
                    <i data-lucide="shield-check" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-black dark:text-white">Status Pemulihan
                    Akun</h1>
                <p class="text-xs sm:text-sm text-black/60 dark:text-white/60">Pantau perkembangan verifikasi berkas
                    permohonan pemulihan akses Anda</p>
            </div>

            @if ($recovery)
                <!-- Ticket Detail Card (Apple Inset Bento Card) -->
                <div class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/50 space-y-6 transition-all"
                    x-data="{ copied: false }">
                    <!-- Ticket Hero Bar -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                        <div>
                            <span
                                class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">Nomor
                                Tiket Permohonan</span>
                            <div class="flex items-center gap-2.5 mt-0.5">
                                <span
                                    class="text-lg sm:text-xl font-mono font-bold text-black dark:text-white tracking-wide">{{ $recovery->ticket_number }}</span>
                                <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $recovery->ticket_number }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="px-2.5 py-1 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/70 dark:text-white/70 text-[11px] font-medium inline-flex items-center gap-1 transition-all"
                                    title="Salin Nomor Tiket">
                                    <i :data-lucide="copied ? 'check' : 'copy'" class="w-3.5 h-3.5"></i>
                                    <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Status Badge (Apple Pill) -->
                        <div>
                            @if ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                                <span
                                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#FF9500] dark:text-[#FF9F0A] text-xs font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-[#FF9500] animate-ping"></span>
                                    <span>Menunggu Review Admin</span>
                                </span>
                            @elseif ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs font-semibold">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                                    <span>Disetujui Resmi</span>
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs font-semibold">
                                    <i data-lucide="x-circle" class="w-4 h-4 text-[#FF3B30] dark:text-[#FF453A]"></i>
                                    <span>Permohonan Ditolak</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Status Announcement Banner -->
                    @if ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                        <div
                            class="p-4 rounded-[20px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-black dark:text-white text-xs sm:text-sm flex items-start gap-3.5">
                            <div
                                class="w-9 h-9 rounded-[14px] bg-[#FF9500]/20 flex items-center justify-center shrink-0 mt-0.5 text-[#FF9500] dark:text-[#FF9F0A]">
                                <i data-lucide="hourglass" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-1">
                                <strong class="font-bold block text-sm sm:text-base text-black dark:text-white">Sedang
                                    Ditinjau Administrator</strong>
                                <p class="text-xs sm:text-sm leading-relaxed text-black/70 dark:text-white/70">
                                    Berkas identitas dan dokumen legalitas usaha Anda sedang diverifikasi secara manual.
                                    Proses peninjauan memakan waktu maksimal 1x24 jam kerja.
                                </p>
                            </div>
                        </div>
                    @elseif ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                        <div
                            class="p-4 rounded-[20px] bg-[#34C759]/10 border border-[#34C759]/25 text-black dark:text-white text-xs sm:text-sm flex items-start gap-3.5">
                            <div
                                class="w-9 h-9 rounded-[14px] bg-[#34C759]/20 flex items-center justify-center shrink-0 mt-0.5 text-[#34C759] dark:text-[#30D158]">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-1">
                                <strong class="font-bold block text-sm sm:text-base text-black dark:text-white">Pemulihan
                                    Akun Berhasil Disetujui</strong>
                                <p class="text-xs sm:text-sm leading-relaxed text-black/70 dark:text-white/70">
                                    Email dan nomor WhatsApp Anda telah resmi diperbarui ke kontak baru. Silakan masuk
                                    menggunakan kredensial yang baru saja disetujui.
                                </p>
                            </div>
                        </div>
                    @else
                        <div
                            class="p-4 rounded-[20px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-black dark:text-white text-xs sm:text-sm flex items-start gap-3.5">
                            <div
                                class="w-9 h-9 rounded-[14px] bg-[#FF3B30]/20 flex items-center justify-center shrink-0 mt-0.5 text-[#FF3B30] dark:text-[#FF453A]">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-1">
                                <strong class="font-bold block text-sm sm:text-base text-black dark:text-white">Permohonan
                                    Belum Dapat Disetujui</strong>
                                <p class="text-xs sm:text-sm leading-relaxed text-black/70 dark:text-white/70">
                                    Catatan Administrator: <span
                                        class="font-semibold italic text-black dark:text-white">"{{ $recovery->rejection_reason ?? 'Dokumen bukti tidak memenuhi standar verifikasi kepemilikan bisnis.' }}"</span>
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Detail Bento Inset Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">Nama
                                Pemohon</span>
                            <span
                                class="font-semibold text-black dark:text-white text-sm">{{ $recovery->applicant_name }}</span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">Nama Bisnis /
                                Toko</span>
                            <span
                                class="font-semibold text-black dark:text-white text-sm">{{ $recovery->business_name }}</span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">Jenis
                                Kendala</span>
                            <span
                                class="font-semibold text-[#007AFF] dark:text-[#0A84FF] text-sm">{{ $recovery->getIssueTypeLabel() }}</span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">Waktu
                                Pengajuan</span>
                            <span
                                class="font-semibold text-black dark:text-white text-sm">{{ $recovery->created_at->translatedFormat('d M Y, H:i') }}
                                WIB</span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">Email Baru
                                Pengganti</span>
                            <span class="font-mono font-semibold text-black dark:text-white text-sm">
                                {{ $recovery->getMaskedNewEmail() }}
                            </span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">Nomor WhatsApp
                                Baru</span>
                            <span class="font-mono font-semibold text-black dark:text-white text-sm">
                                {{ $recovery->getMaskedNewPhone() }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex flex-col sm:flex-row gap-3">
                        @if ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                            <a href="{{ route('login') }}"
                                class="flex-1 min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm text-center shadow-lg shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                                <span>Masuk ke Dashboard Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        @elseif ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_REJECTED)
                            <a href="{{ route('account-recovery.create') }}"
                                class="flex-1 min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#FF3B30] hover:bg-[#E0352B] text-white font-semibold text-sm text-center shadow-lg shadow-[#FF3B30]/25 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                <span>Ajukan Ulang dengan Dokumen Baru</span>
                            </a>
                        @else
                            <button type="button" onclick="location.reload()"
                                class="flex-1 min-h-[50px] py-3.5 px-5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white font-semibold text-sm text-center border border-black/5 dark:border-white/10 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                                <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                                <span>Segarkan Status Terbaru</span>
                            </button>
                        @endif

                        <a href="{{ route('landing') }}"
                            class="min-h-[50px] py-3.5 px-5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.1] border border-black/5 dark:border-white/10 text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold text-sm text-center transition-all flex items-center justify-center">
                            Beranda
                        </a>
                    </div>
                </div>
            @else
                <!-- Search Form Card (Apple HIG Inset Grouped) -->
                <div
                    class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/50 transition-all">
                    @if ($errors->any())
                        <div
                            class="mb-5 p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs sm:text-sm flex items-center gap-2.5 animate-shake">
                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('account-recovery.check') }}" class="space-y-5">
                        <div>
                            <label for="ticket"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 uppercase tracking-wider mb-2">
                                Nomor Tiket Pemulihan <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                    <i data-lucide="ticket" class="w-5 h-5"></i>
                                </div>
                                <input type="text" name="ticket" id="ticket" required
                                    placeholder="Contoh: REC-202609-AB12CD"
                                    class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[16px] text-black dark:text-white font-mono placeholder:text-black/35 dark:placeholder:text-white/35 text-[16px] sm:text-sm uppercase transition-all focus:outline-none focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15">
                            </div>
                            <p class="mt-1.5 text-xs text-black/50 dark:text-white/50">Masukkan nomor tiket yang Anda
                                terima saat mengirim permohonan pemulihan.</p>
                        </div>

                        <button type="submit"
                            class="w-full min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                            <i data-lucide="search" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            <span>Cari Status Permohonan</span>
                        </button>
                    </form>

                    <div
                        class="mt-6 pt-5 border-t border-black/[0.06] dark:border-white/[0.08] text-center text-xs sm:text-sm text-black/60 dark:text-white/60">
                        Belum mengajukan pemulihan akun?
                        <a href="{{ route('account-recovery.create') }}"
                            class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors ml-1">
                            Buat Pengajuan Baru
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
