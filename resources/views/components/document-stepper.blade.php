@props(['approvalData', 'documentType' => 'purchase_order', 'documentId' => ''])

@php
    $hasApproval = $approvalData['has_approval'] ?? false;
    $status = $approvalData['status'] ?? 'direct';
    $currentLevel = (int) ($approvalData['current_level'] ?? 1);
    $totalLevels = (int) ($approvalData['total_levels'] ?? 1);
    $canApprove = (bool) ($approvalData['can_approve'] ?? false);
    $request = $approvalData['request'] ?? null;
    $logs = $request ? $request->logs : collect();
@endphp

@if ($hasApproval && $request)
    <div
        x-data="{ showApproveModal: false, showRejectModal: false, rejectReason: '', approveNotes: '' }"
        class="glass-card bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/[0.08] dark:border-white/[0.08] rounded-[22px] p-5 sm:p-6 shadow-sm mb-6 relative overflow-hidden">
        
        <!-- Header Stepper -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-black dark:text-white tracking-tight">
                        Tata Kelola Otorisasi Bertingkat (MAR)
                    </h4>
                    <p class="text-xs text-black/55 dark:text-white/55">
                        Dokumen memerlukan persetujuan berjenjang sebelum dapat dicairkan atau dikonfirmasi.
                    </p>
                </div>
            </div>

            <!-- Status Pill Badge -->
            <div>
                @if ($status === 'approved')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/25">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                        <span>Disetujui Sepenuhnya (Siap Eksekusi)</span>
                    </span>
                @elseif ($status === 'rejected')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#FF3B30]/15 text-[#FF3B30] border border-[#FF3B30]/25">
                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                        <span>Ditolak oleh Penyetuju</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/25">
                        <i data-lucide="clock" class="w-3.5 h-3.5 animate-pulse"></i>
                        <span>Menunggu Persetujuan Level {{ $currentLevel }} dari {{ $totalLevels }}</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Horizontal Track Stepper -->
        <div class="relative py-3">
            <div class="grid grid-cols-1 sm:grid-cols-{{ $totalLevels + 2 }} gap-4 sm:gap-2">
                
                <!-- 1. Maker (Draf Dibuat) -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#34C759] text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider font-extrabold text-[#34C759]">Maker (Draf)</div>
                        <div class="text-xs font-semibold text-black dark:text-white truncate max-w-[120px]">
                            {{ $request->requester->name ?? 'Staf Pembuat' }}
                        </div>
                    </div>
                </div>

                <!-- 2. Intermediate Approver Levels -->
                @for ($lvl = 1; $lvl <= $totalLevels; $lvl++)
                    @php
                        $roleName = $request->rule?->getRoleForLevel($lvl) ?? "Level $lvl";
                        $log = $logs->firstWhere('level', $lvl);
                        $isCurrent = ($status === 'pending' && $currentLevel === $lvl);
                        $isPassed = ($log && $log->action === 'approved') || ($status === 'pending' && $currentLevel > $lvl) || ($status === 'approved');
                        $isRejectedHere = ($status === 'rejected' && $currentLevel === $lvl);
                    @endphp

                    <div class="flex items-center gap-3">
                        @if ($isRejectedHere)
                            <div class="w-8 h-8 rounded-full bg-[#FF3B30] text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="x" class="w-4 h-4 stroke-[3]"></i>
                            </div>
                        @elseif ($isPassed)
                            <div class="w-8 h-8 rounded-full bg-[#34C759] text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                            </div>
                        @elseif ($isCurrent)
                            <div class="w-8 h-8 rounded-full bg-[#FF9500] text-white flex items-center justify-center shrink-0 shadow-md ring-4 ring-[#FF9500]/20 animate-pulse">
                                <span class="text-xs font-bold">{{ $lvl }}</span>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40 flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold">{{ $lvl }}</span>
                            </div>
                        @endif

                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-extrabold {{ $isRejectedHere ? 'text-[#FF3B30]' : ($isPassed ? 'text-[#34C759]' : ($isCurrent ? 'text-[#FF9500]' : 'text-black/40 dark:text-white/40')) }}">
                                Approver L{{ $lvl }}
                            </div>
                            <div class="text-xs font-semibold text-black dark:text-white capitalize">
                                {{ $log?->approver?->name ?? ucfirst($roleName) }}
                            </div>
                        </div>
                    </div>
                @endfor

                <!-- 3. Releaser (Pencairan / Rilis) -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full {{ $status === 'approved' ? 'bg-[#007AFF] text-white' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40' }} flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider font-extrabold {{ $status === 'approved' ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40' }}">
                            Releaser
                        </div>
                        <div class="text-xs font-semibold text-black dark:text-white">
                            {{ $status === 'approved' ? 'Siap Dieksekusi' : 'Terkunci' }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Reason Banner if Rejected -->
        @if ($status === 'rejected' && !empty($request->rejection_reason))
            <div class="mt-4 p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] dark:text-[#FF453A] text-xs flex items-start gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                <div>
                    <span class="font-bold">Alasan Penolakan:</span>
                    <p class="mt-0.5 opacity-90">{{ $request->rejection_reason }}</p>
                </div>
            </div>
        @endif

        <!-- Quick Action Bar if User is Eligible Approver -->
        @if ($canApprove && $status === 'pending')
            <div class="mt-5 pt-4 border-t border-black/[0.06] dark:border-white/[0.06] flex flex-wrap items-center justify-between gap-3 bg-black/[0.02] dark:bg-white/[0.02] p-4 rounded-[18px]">
                <div class="text-xs text-black/75 dark:text-white/75 font-medium flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Anda memiliki wewenang untuk mengambil tindakan atas dokumen ini pada <strong>Level {{ $currentLevel }}</strong>.</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <button
                        type="button"
                        @click="showRejectModal = true"
                        class="min-h-[42px] px-4 py-2 rounded-[14px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] dark:text-[#FF453A] font-semibold text-xs transition-all active:scale-95 flex items-center gap-1.5">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        <span>Tolak Dokumen</span>
                    </button>
                    <button
                        type="button"
                        @click="showApproveModal = true"
                        class="min-h-[42px] px-5 py-2 rounded-[14px] bg-[#34C759] hover:bg-[#2FB34F] text-white font-semibold text-xs shadow-md shadow-[#34C759]/20 transition-all active:scale-95 flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Setujui (Level {{ $currentLevel }})</span>
                    </button>
                </div>
            </div>

            <!-- Modal Sheet Konfirmasi Setujui -->
            <div
                x-show="showApproveModal"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm animate-fade-in"
                @keydown.escape.window="showApproveModal = false">
                <div
                    @click.outside="showApproveModal = false"
                    class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[16px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                            <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-black dark:text-white">Konfirmasi Persetujuan</h3>
                            <p class="text-xs text-black/55 dark:text-white/55">Persetujuan Otorisasi Level {{ $currentLevel }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('approvals.approve', $request->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                Catatan Persetujuan (Opsional):
                            </label>
                            <textarea
                                name="notes"
                                rows="2"
                                x-model="approveNotes"
                                placeholder="Contoh: Anggaran telah divalidasi sesuai pagu bulanan."
                                class="w-full px-3.5 py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white outline-none resize-none focus:border-[#34C759]"></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2.5 pt-2">
                            <button
                                type="button"
                                @click="showApproveModal = false"
                                class="min-h-[40px] px-4 py-2 rounded-[14px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="min-h-[40px] px-5 py-2 rounded-[14px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-xs font-semibold shadow-md">
                                Ya, Setujui Dokumen
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Sheet Konfirmasi Tolak -->
            <div
                x-show="showRejectModal"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm animate-fade-in"
                @keydown.escape.window="showRejectModal = false">
                <div
                    @click.outside="showRejectModal = false"
                    class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[16px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-black dark:text-white">Tolak Dokumen</h3>
                            <p class="text-xs text-black/55 dark:text-white/55">Penolakan wajib menyertakan alasan tertulis</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('approvals.reject', $request->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                Alasan Penolakan <span class="text-[#FF3B30]">*</span>
                            </label>
                            <textarea
                                name="reason"
                                rows="3"
                                required
                                x-model="rejectReason"
                                placeholder="Jelaskan alasan dokumen ditolak agar staf pembuat dapat melakukan revisi..."
                                class="w-full px-3.5 py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white outline-none resize-none focus:border-[#FF3B30]"></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2.5 pt-2">
                            <button
                                type="button"
                                @click="showRejectModal = false"
                                class="min-h-[40px] px-4 py-2 rounded-[14px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="!rejectReason.trim()"
                                class="min-h-[40px] px-5 py-2 rounded-[14px] bg-[#FF3B30] hover:bg-[#E0352B] disabled:opacity-50 text-white text-xs font-semibold shadow-md">
                                Tolak Dokumen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
@endif
