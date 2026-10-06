@extends('layouts.app', ['title' => __('ai.actions.title') . ''])

@section('content')
<div class="space-y-6" x-data="actionCenterApp()">

    <!-- Unified Apple HIG Navigation Hub -->
    @include('app.ai.partials.office_navigation', ['activeOffice' => 'actions', 'pendingCount' => $counts['pending'] ?? 0])

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('cooca-ai.index') }}" class="text-xs text-black/50 hover:text-black dark:text-white/50 dark:hover:text-white flex items-center gap-1 transition">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.navigation.back_to_office') }}</span>
                </a>
            </div>
            <h1 class="text-2xl font-bold text-black dark:text-white tracking-tight mt-1">{{ __('ai.actions.title') }}</h1>
            <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">{{ __('ai.actions.subtitle') }}</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-black/50 dark:text-white/50 font-mono">
                {{ __('ai.actions.total_proposals', ['count' => $counts['all']]) }}
            </span>
        </div>
    </div>

    <!-- Filter Tabs (Segmented Control Apple HIG) -->
    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 overflow-x-auto">
        <a href="{{ route('cooca-ai.actions', ['status' => 'all']) }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $filter === 'all' ? 'bg-white dark:bg-zinc-800 text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            {{ __('ai.actions.tabs.all', ['count' => $counts['all']]) }}
        </a>
        <a href="{{ route('cooca-ai.actions', ['status' => 'pending']) }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $filter === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            {{ __('ai.actions.tabs.pending', ['count' => $counts['pending']]) }}
        </a>
        <a href="{{ route('cooca-ai.actions', ['status' => 'approved']) }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $filter === 'approved' ? 'bg-white dark:bg-zinc-800 text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            {{ __('ai.actions.tabs.approved', ['count' => $counts['approved']]) }}
        </a>
        <a href="{{ route('cooca-ai.actions', ['status' => 'completed']) }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $filter === 'completed' ? 'bg-white dark:bg-zinc-800 text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            {{ __('ai.actions.tabs.completed', ['count' => $counts['completed']]) }}
        </a>
        <a href="{{ route('cooca-ai.actions', ['status' => 'rejected']) }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $filter === 'rejected' ? 'bg-white dark:bg-zinc-800 text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            {{ __('ai.actions.tabs.rejected', ['count' => $counts['rejected']]) }}
        </a>
        <a href="{{ route('cooca-ai.actions', ['status' => 'failed']) }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $filter === 'failed' ? 'bg-white dark:bg-zinc-800 text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            {{ __('ai.actions.tabs.failed', ['count' => $counts['failed']]) }}
        </a>
    </div>

    <!-- Action Cards List -->
    @if($proposals->isEmpty())
        <div class="p-12 text-center rounded-2xl bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 space-y-2">
            <div class="w-10 h-10 rounded-xl bg-black/5 dark:bg-white/5 mx-auto flex items-center justify-center text-black/40 dark:text-white/40">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div class="text-xs font-bold text-black dark:text-white">{{ __('ai.actions.no_data') }}</div>
            <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.actions.no_data_desc') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach($proposals as $prop)
                <div x-show="!processedProposalIds.includes('{{ $prop->id }}')"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    
                    <!-- Left: Info & Badges -->
                    <div class="space-y-1.5 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold text-black dark:text-white">{{ $prop->title }}</span>
                            
                            <!-- Status Badge -->
                            @if($prop->status === 'pending')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    {{ __('ai.actions.status.pending') }}
                                </span>
                            @elseif($prop->status === 'completed')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    {{ __('ai.actions.status.completed') }}
                                </span>
                            @elseif($prop->status === 'approved')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                    {{ __('ai.actions.status.approved') }}
                                </span>
                            @elseif($prop->status === 'rejected')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                    {{ __('ai.actions.status.rejected') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-black/5 text-black/60 dark:bg-white/10 dark:text-white/60">
                                    {{ strtoupper($prop->status) }}
                                </span>
                            @endif

                            <!-- Risk Badge -->
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold {{ in_array($prop->risk_level, ['HIGH', 'CRITICAL']) ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400' : 'bg-blue-500/10 text-blue-600 dark:text-blue-400' }}">
                                {{ __('ai.actions.risk', ['level' => $prop->risk_level]) }}
                            </span>
                        </div>

                        <p class="text-xs text-black/70 dark:text-white/70">{{ $prop->description }}</p>

                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-black/50 dark:text-white/50 pt-1">
                            <span>{{ __('ai.actions.division') }}: <strong class="text-black/80 dark:text-white/80 capitalize">{{ $prop->department }}</strong></span>
                            &bull;
                            <span>{{ __('ai.actions.agent') }}: <strong class="text-black/80 dark:text-white/80 capitalize">{{ $prop->agent }} Agent</strong></span>
                            @if($prop->estimated_cost > 0)
                                &bull;
                                <span>{{ __('ai.actions.cost_estimate') }}: <strong class="text-black dark:text-white font-mono">Rp {{ number_format((float)$prop->estimated_cost, 0, ',', '.') }}</strong></span>
                            @endif
                            &bull;
                            <span>{{ __('ai.actions.time') }}: {{ $prop->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-2 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-black/5 dark:border-white/5">
                        
                        <!-- Review Button (Opens Apple HIG Modal) -->
                        <button type="button" 
                                @click="openReviewModal(@js($prop))"
                                class="px-3.5 py-2 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-semibold text-black dark:text-white transition flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>{{ __('ai.actions.review_detail') }}</span>
                        </button>

                        @if($prop->isPending() || $prop->isApproved())
                            @if($prop->isPending())
                                <!-- Reject Button -->
                                <button type="button" 
                                        :disabled="isSubmitting"
                                        :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isSubmitting }"
                                        @click="rejectProposal('{{ $prop->id }}', '{{ $prop->title }}')"
                                        class="px-3 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-semibold transition">
                                    {{ __('ai.actions.reject') }}
                                </button>
                            @endif

                            <!-- Approve / Execute Button -->
                            <button type="button" 
                                    :disabled="isSubmitting"
                                    :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isSubmitting }"
                                    @click="approveProposal('{{ $prop->id }}')"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                                <template x-if="isSubmitting && actionInProgressId === '{{ $prop->id }}'">
                                    <svg class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </template>
                                <template x-if="!isSubmitting || actionInProgressId !== '{{ $prop->id }}'">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </template>
                                <span>{{ $prop->isApproved() ? __('ai.actions.execute') : __('ai.actions.approve_execute') }}</span>
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $proposals->links() }}
        </div>
    @endif

    <!-- FULL-SIZE REVIEW MODAL SHEET (WHY, WHAT, IMPACT, COST, DATA, AGENT, CHANGES) -->
    <div x-show="showReviewModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6" 
         style="display: none;">
        
        <div class="w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] bg-white dark:bg-zinc-900 rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden"
             @click.away="closeReviewModal()">
            
            <div class="p-5 border-b border-black/10 dark:border-white/10 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('ai.actions.review_title') }}</span>
                    <h3 class="text-base font-bold text-black dark:text-white mt-0.5" x-text="selectedProposal?.title"></h3>
                </div>
                <button @click="closeReviewModal()" class="p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 text-black/50 dark:text-white/50">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-5 text-xs sm:text-sm">
                
                <!-- 1. WHY -->
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                    <div class="text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.actions.why_title') }}</div>
                    <p class="text-black/80 dark:text-white/80 leading-relaxed font-medium" x-text="selectedProposal?.reason"></p>
                </div>

                <!-- 2. WHAT -->
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                    <div class="text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.actions.what_title') }}</div>
                    <p class="text-black/80 dark:text-white/80 leading-relaxed" x-text="selectedProposal?.description"></p>
                </div>

                <!-- 3. IMPACT & METRICS -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                        <div class="text-[10px] font-semibold text-black/50 dark:text-white/50 uppercase">{{ __('ai.actions.risk_level') }}</div>
                        <div class="text-xs font-bold text-black dark:text-white mt-1" x-text="selectedProposal?.risk_level"></div>
                    </div>
                    <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                        <div class="text-[10px] font-semibold text-black/50 dark:text-white/50 uppercase">{{ __('ai.actions.estimated_cost') }}</div>
                        <div class="text-xs font-bold font-mono text-black dark:text-white mt-1">
                            Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedProposal?.estimated_cost || 0)"></span>
                        </div>
                    </div>
                    <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                        <div class="text-[10px] font-semibold text-black/50 dark:text-white/50 uppercase">{{ __('ai.actions.agent_team') }}</div>
                        <div class="text-xs font-bold text-black dark:text-white capitalize mt-1" x-text="selectedProposal?.agent + ' Agent'"></div>
                    </div>
                </div>

                <!-- 4. REVISION PANEL (HUMAN-IN-THE-LOOP ADJUSTMENT) -->
                <template x-if="selectedProposal?.status === 'pending' || selectedProposal?.status === 'approved'">
                    <div class="p-4 rounded-2xl bg-amber-500/[0.06] border border-amber-500/20 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="edit-3" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                                <span class="text-xs font-bold text-amber-900 dark:text-amber-300">{{ __('ai.actions.revision_mode') }}</span>
                            </div>
                            <button type="button" @click="isRevising = !isRevising" class="text-xs font-semibold text-amber-700 dark:text-amber-400 hover:underline">
                                <span x-text="isRevising ? '{{ __('ai.actions.close_revision') }}' : '{{ __('ai.actions.open_revision') }}'"></span>
                            </button>
                        </div>

                        <div x-show="isRevising" x-transition class="space-y-3 pt-2 border-t border-amber-500/20">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('ai.actions.quantity_target') }}</label>
                                    <input type="number" x-model.number="editQuantity" min="1" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-zinc-800 border border-black/10 dark:border-white/10 text-xs font-mono font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('ai.actions.unit_price') }}</label>
                                    <input type="number" x-model.number="editUnitPrice" min="0" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-zinc-800 border border-black/10 dark:border-white/10 text-xs font-mono font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                            </div>

                            <div class="p-2.5 rounded-xl bg-white dark:bg-zinc-800 border border-amber-500/20 flex items-center justify-between text-xs">
                                <span class="text-black/60 dark:text-white/60">{{ __('ai.actions.new_total_cost') }}</span>
                                <strong class="text-black dark:text-white font-mono text-sm">
                                    Rp <span x-text="new Intl.NumberFormat('id-ID').format((editQuantity || 0) * (editUnitPrice || 0))"></span>
                                </strong>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('ai.actions.revision_notes') }}</label>
                                <input type="text" x-model="editNotes" placeholder="{{ __('ai.actions.revision_placeholder') }}" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-zinc-800 border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div class="flex justify-end pt-1">
                                <button type="button" 
                                        :disabled="isSubmitting"
                                        :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isSubmitting }"
                                        @click="saveRevision(selectedProposal.id)" 
                                        class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition flex items-center gap-1.5">
                                    <template x-if="isSubmitting && actionInProgressId === selectedProposal?.id">
                                        <svg class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!isSubmitting || actionInProgressId !== selectedProposal?.id">
                                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                    </template>
                                    <span x-text="isSubmitting && actionInProgressId === selectedProposal?.id ? '{{ __('ai.actions.saving') }}' : '{{ __('ai.actions.save_revision') }}'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- 5. CHANGES / PAYLOAD PREVIEW (HUMAN-READABLE LIST FIRST) -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="list-checks" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                            <span>{{ __('ai.actions.parameter_details') }}</span>
                        </div>
                        <button type="button" 
                                @click="showRawPayload = !showRawPayload" 
                                class="text-[10px] font-semibold text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white transition flex items-center gap-1 px-2 py-0.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/5">
                            <i data-lucide="code" class="w-3 h-3"></i>
                            <span x-text="showRawPayload ? '{{ __('ai.actions.view_mode_list') }}' : '{{ __('ai.actions.view_raw_json') }}'"></span>
                        </button>
                    </div>

                    <!-- Clean Structured List Mode (Default) -->
                    <div x-show="!showRawPayload" class="rounded-2xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] overflow-hidden">
                        <!-- Key-Value List Items -->
                        <div class="divide-y divide-black/5 dark:divide-white/5">
                            <template x-for="[key, val] in getPayloadEntries(selectedProposal?.payload)" :key="key">
                                <div class="px-4 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                                    <span class="font-medium text-black/60 dark:text-white/60" x-text="formatKey(key)"></span>
                                    <span class="font-semibold text-black dark:text-white font-mono text-left sm:text-right" x-text="formatValue(key, val)"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Itemized Table if payload has items -->
                        <template x-if="selectedProposal?.payload?.items && Array.isArray(selectedProposal.payload.items) && selectedProposal.payload.items.length > 0">
                            <div class="border-t border-black/10 dark:border-white/10 p-3.5 bg-white dark:bg-zinc-800/40 space-y-2">
                                <div class="text-[11px] font-bold text-black/70 dark:text-white/70">{{ __('ai.actions.order_items_list') }}</div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead>
                                            <tr class="border-b border-black/5 dark:border-white/10 text-black/50 dark:text-white/50 text-[10px] uppercase font-bold">
                                                <th class="pb-1.5">{{ __('ai.actions.item_name') }}</th>
                                                <th class="pb-1.5 text-right">{{ __('ai.actions.item_qty') }}</th>
                                                <th class="pb-1.5 text-right">{{ __('ai.actions.item_unit_price') }}</th>
                                                <th class="pb-1.5 text-right">{{ __('ai.actions.item_subtotal') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                                            <template x-for="item in selectedProposal.payload.items" :key="item.product_id || item.product_name">
                                                <tr>
                                                    <td class="py-2 font-medium text-black dark:text-white" x-text="item.product_name || item.name || 'Produk'"></td>
                                                    <td class="py-2 text-right font-mono text-black/70 dark:text-white/70" x-text="item.quantity"></td>
                                                    <td class="py-2 text-right font-mono text-black/70 dark:text-white/70">
                                                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(item.unit_cost || item.unit_price || 0)"></span>
                                                    </td>
                                                    <td class="py-2 text-right font-mono font-bold text-black dark:text-white">
                                                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(item.total || (item.quantity * (item.unit_cost || item.unit_price || 0)))"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Raw JSON Mode (Secondary/Audit) -->
                    <pre x-show="showRawPayload" class="p-3.5 rounded-2xl bg-black/[0.03] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 text-[11px] font-mono text-black/80 dark:text-white/80 overflow-x-auto max-h-48" x-text="JSON.stringify(selectedProposal?.payload, null, 2)"></pre>
                </div>

                <!-- 6. WHATSAPP DIRECT SUPPLIER DISPATCH (FOR PURCHASE ORDERS) -->
                <template x-if="selectedProposal?.result?.whatsapp_url">
                    <div class="p-4 rounded-2xl bg-emerald-500/[0.08] border border-emerald-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-0.5">
                            <div class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                                <i data-lucide="message-square" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                <span>{{ __('ai.actions.whatsapp_ready') }}</span>
                            </div>
                            <div class="text-[11px] text-emerald-700/80 dark:text-emerald-400/80">
                                Penerima: <strong x-text="selectedProposal.result.supplier_name"></strong> (<span x-text="selectedProposal.result.supplier_phone || '-'"></span>)
                            </div>
                        </div>
                        <a :href="selectedProposal.result.whatsapp_url" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shrink-0 shadow-sm">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>{{ __('ai.actions.whatsapp_button') }}</span>
                        </a>
                    </div>
                </template>

                <!-- 7. OWNER CONFIRMATION NOTIFICATION BANNER -->
                <template x-if="selectedProposal?.result?.notification_to_owner">
                    <div class="p-3.5 rounded-xl bg-blue-500/[0.08] border border-blue-500/20 text-xs text-blue-800 dark:text-blue-300 flex items-start gap-2.5">
                        <i data-lucide="bell" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5"></i>
                        <div class="space-y-0.5">
                            <div class="font-bold" x-text="selectedProposal.result.notification_to_owner.title"></div>
                            <div class="text-[11px] text-blue-700/80 dark:text-blue-300/80" x-text="selectedProposal.result.notification_to_owner.message"></div>
                        </div>
                    </div>
                </template>

                <!-- 8. EXECUTION RESULT (HUMAN-READABLE LIST FIRST) -->
                <template x-if="selectedProposal?.result">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ __('ai.actions.execution_result') }}</span>
                            </div>
                            <button type="button" 
                                    @click="showRawResult = !showRawResult" 
                                    class="text-[10px] font-semibold text-emerald-600/70 hover:text-emerald-600 dark:text-emerald-400/70 dark:hover:text-emerald-400 transition flex items-center gap-1 px-2 py-0.5 rounded-lg hover:bg-emerald-500/10">
                                <i data-lucide="code" class="w-3 h-3"></i>
                                <span x-text="showRawResult ? '{{ __('ai.actions.view_mode_list') }}' : '{{ __('ai.actions.view_raw_json') }}'"></span>
                            </button>
                        </div>

                        <!-- Clean Human-Readable List Mode (Default) -->
                        <div x-show="!showRawResult" class="space-y-2.5">
                            
                            <!-- Audit Meta Pill Bar if present -->
                            <template x-if="selectedProposal.result.audit_ref || selectedProposal.result.audited_at || selectedProposal.result.audited_by">
                                <div class="p-3 rounded-2xl bg-emerald-500/[0.08] border border-emerald-500/20 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs">
                                    <template x-if="selectedProposal.result.audit_ref">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] uppercase font-bold text-emerald-700/70 dark:text-emerald-400/70">Ref Audit:</span>
                                            <span class="font-mono font-bold text-emerald-900 dark:text-emerald-200" x-text="selectedProposal.result.audit_ref"></span>
                                        </div>
                                    </template>
                                    <template x-if="selectedProposal.result.audited_by">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] uppercase font-bold text-emerald-700/70 dark:text-emerald-400/70">Oleh:</span>
                                            <span class="font-semibold text-emerald-900 dark:text-emerald-200" x-text="selectedProposal.result.audited_by"></span>
                                        </div>
                                    </template>
                                    <template x-if="selectedProposal.result.audited_at">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] uppercase font-bold text-emerald-700/70 dark:text-emerald-400/70">Waktu:</span>
                                            <span class="font-mono text-emerald-800 dark:text-emerald-300" x-text="formatValue('audited_at', selectedProposal.result.audited_at)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Action Plan List (Numbered Taktis Items) -->
                            <template x-if="getActionPlan(selectedProposal.result).length > 0">
                                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.04] p-4 space-y-2.5">
                                    <div class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="compass" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>Rencana Aksi &amp; Langkah Taktis (Action Plan):</span>
                                    </div>
                                    <ul class="space-y-2 pt-0.5">
                                        <template x-for="(plan, idx) in getActionPlan(selectedProposal.result)" :key="idx">
                                            <li class="flex items-start gap-2.5 text-xs text-emerald-950 dark:text-emerald-100 leading-relaxed">
                                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-bold font-mono text-[10px] flex items-center justify-center shrink-0 mt-0.5" x-text="idx + 1"></span>
                                                <span class="flex-1 font-medium" x-text="plan"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>

                            <!-- Key-Value Result Items -->
                            <template x-if="getResultEntries(selectedProposal.result).length > 0">
                                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.03] overflow-hidden divide-y divide-emerald-500/10">
                                    <template x-for="[key, val] in getResultEntries(selectedProposal.result)" :key="key">
                                        <div class="px-4 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                                            <span class="font-medium text-emerald-800/80 dark:text-emerald-300/80" x-text="formatKey(key)"></span>
                                            <span class="font-semibold text-emerald-950 dark:text-emerald-100 font-mono text-left sm:text-right" x-text="formatValue(key, val)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                        </div>

                        <!-- Raw JSON Mode (Secondary/Audit) -->
                        <pre x-show="showRawResult" class="p-3.5 rounded-2xl bg-emerald-500/[0.06] border border-emerald-500/20 text-[11px] font-mono text-emerald-800 dark:text-emerald-300 overflow-x-auto max-h-48" x-text="JSON.stringify(selectedProposal?.result, null, 2)"></pre>
                    </div>
                </template>

                <!-- 9. ERROR FEEDBACK (IF FAILED) -->
                <template x-if="selectedProposal?.error">
                    <div class="p-3.5 rounded-xl bg-rose-500/[0.08] border border-rose-500/20 text-xs text-rose-700 dark:text-rose-300 flex items-start gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                        <div>
                            <div class="font-bold">Catatan Kendala Eksekusi:</div>
                            <div class="mt-0.5 font-mono text-[11px]" x-text="selectedProposal.error"></div>
                        </div>
                    </div>
                </template>

            </div>

            <!-- Footer Action Buttons -->
            <div class="p-4 border-t border-black/10 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                <button type="button" @click="closeReviewModal()" class="px-3.5 py-2 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-semibold text-black dark:text-white transition">
                    {{ __('common.close') }}
                </button>

                <template x-if="selectedProposal?.status === 'pending' || selectedProposal?.status === 'approved'">
                    <div class="flex items-center gap-2">
                        <template x-if="selectedProposal?.status === 'pending'">
                            <button type="button" 
                                    :disabled="isSubmitting"
                                    :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isSubmitting }"
                                    @click="rejectProposal(selectedProposal.id, selectedProposal.title)" 
                                    class="px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-semibold transition">
                                {{ __('ai.actions.reject') }}
                            </button>
                        </template>
                        <button type="button" 
                                :disabled="isSubmitting"
                                :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isSubmitting }"
                                @click="approveProposal(selectedProposal.id)" 
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                            <template x-if="isSubmitting && actionInProgressId === selectedProposal?.id">
                                <svg class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </template>
                            <template x-if="!isSubmitting || actionInProgressId !== selectedProposal?.id">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            </template>
                            <span x-text="isSubmitting && actionInProgressId === selectedProposal?.id ? '{{ __('common.processing') }}' : (selectedProposal?.status === 'approved' ? '{{ __('ai.actions.execute') }}' : '{{ __('ai.actions.approve_execute') }}')"></span>
                        </button>
                    </div>
                </template>
            </div>

        </div>
    </div>

</div>

<script>
function actionCenterApp() {
    return {
        showReviewModal: false,
        selectedProposal: null,
        isRevising: false,
        isSubmitting: false,
        actionInProgressId: null,
        processedProposalIds: [],
        showRawPayload: false,
        showRawResult: false,
        editQuantity: 1,
        editUnitPrice: 0,
        editNotes: '',

        formatKey(key) {
            const keyMap = {
                'period': 'Periode Target',
                'current_revenue': 'Pendapatan Riil Saat Ini',
                'current_expenses': 'Beban Pengeluaran Saat Ini',
                'efficiency_ratio': 'Rasio Efisiensi Biaya',
                'target_efficiency_ratio': 'Target Rasio Efisiensi',
                'required_revenue_for_breakeven': 'Target Omzet Impas (Break-Even)',
                'supplier_id': 'ID Supplier',
                'supplier_name': 'Nama Pemasok / Supplier',
                'supplier_phone': 'No. WhatsApp Supplier',
                'customer_id': 'ID Pelanggan',
                'customer_name': 'Nama Pelanggan / Klien',
                'due_date': 'Tanggal Jatuh Tempo',
                'notes': 'Catatan / Dasar Pertimbangan',
                'quantity': 'Jumlah Kuantitas',
                'unit_price': 'Estimasi Harga Satuan',
                'unit_cost': 'Biaya Modal Satuan',
                'total_amount': 'Total Nominal',
                'discount_percent': 'Diskon',
                'payment_method': 'Metode Pembayaran',
                'channel': 'Kanal Distribusi',
                'target_audience': 'Target Audiens',
                'audit_ref': 'Nomor Referensi Audit',
                'audited_at': 'Waktu Disahkan',
                'audited_by': 'Disahkan Oleh',
                'po_number': 'Nomor Purchase Order',
                'po_id': 'ID Purchase Order',
                'invoice_number': 'Nomor Faktur Tagihan',
                'invoice_id': 'ID Faktur',
                'status': 'Status Eksekusi',
                'message': 'Ringkasan Eksekusi',
                'product_name': 'Nama Produk / Bahan',
            };
            const i18nLabels = window.COOCA_I18N?.ai?.field_labels || {};
            if (i18nLabels[key]) return i18nLabels[key];
            if (keyMap[key]) return keyMap[key];
            return String(key).replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        formatValue(key, val) {
            if (val === null || val === undefined) return '-';
            const locale = window.COOCA_LOCALE === 'en' ? 'en-US' : 'id-ID';
            const currPrefix = window.COOCA_LOCALE === 'en' ? 'IDR ' : 'Rp ';

            if (typeof val === 'boolean') {
                return val ? (window.COOCA_LOCALE === 'en' ? 'Yes (Active)' : 'Ya (Aktif)') : (window.COOCA_LOCALE === 'en' ? 'No' : 'Tidak');
            }
            if (typeof val === 'number') {
                const lowerKey = String(key).toLowerCase();
                if (lowerKey.includes('revenue') || lowerKey.includes('expense') || lowerKey.includes('cost') || lowerKey.includes('price') || lowerKey.includes('amount') || lowerKey.includes('breakeven')) {
                    return currPrefix + new Intl.NumberFormat(locale).format(val);
                }
                if (lowerKey.includes('ratio')) {
                    return val + 'x';
                }
                if (lowerKey.includes('percent') || lowerKey.includes('discount')) {
                    return val + '%';
                }
                return new Intl.NumberFormat(locale).format(val);
            }
            if (typeof val === 'string') {
                if (val.match(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/)) {
                    try {
                        const d = new Date(val);
                        return d.toLocaleDateString(locale, { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + (window.COOCA_LOCALE === 'en' ? ' (UTC+7)' : ' WIB');
                    } catch (e) {
                        return val;
                    }
                }
            }
            if (typeof val === 'object') {
                return JSON.stringify(val);
            }
            return String(val);
        },

        getPayloadEntries(payload) {
            if (!payload || typeof payload !== 'object') return [];
            return Object.entries(payload).filter(([k, v]) => k !== 'items' && !Array.isArray(v) && typeof v !== 'object');
        },

        getResultEntries(result) {
            if (!result || typeof result !== 'object') return [];
            return Object.entries(result).filter(([k, v]) => {
                return !['action_plan', 'audit_ref', 'audited_at', 'audited_by', 'notification_to_owner', 'whatsapp_url'].includes(k) && !Array.isArray(v) && typeof v !== 'object';
            });
        },

        getActionPlan(result) {
            if (!result || typeof result !== 'object') return [];
            if (Array.isArray(result.action_plan)) return result.action_plan;
            return [];
        },

        openReviewModal(prop) {
            this.selectedProposal = prop;
            this.isRevising = false;
            this.showRawPayload = false;
            this.showRawResult = false;

            const p = prop.payload || {};
            if (p.items && Array.isArray(p.items) && p.items.length > 0) {
                this.editQuantity = Number(p.items[0].quantity) || 1;
                this.editUnitPrice = Number(p.items[0].unit_cost || p.items[0].unit_price) || 0;
            } else {
                this.editQuantity = Number(p.quantity) || 1;
                this.editUnitPrice = Number(p.unit_price || p.unit_cost) || (prop.estimated_cost ? Math.round(Number(prop.estimated_cost) / (this.editQuantity || 1)) : 0);
            }
            this.editNotes = p.notes || '';

            this.showReviewModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        closeReviewModal() {
            this.showReviewModal = false;
            this.selectedProposal = null;
            this.isRevising = false;
        },

        async saveRevision(id) {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            this.actionInProgressId = id;

            try {
                const targetUrl = "{{ route('cooca-ai.actions.revise', ':id') }}".replace(':id', id);
                const res = await fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        payload: {
                            quantity: this.editQuantity,
                            unit_price: this.editUnitPrice,
                            notes: this.editNotes
                        }
                    })
                });

                const data = await res.json();
                if (data.success) {
                    if (window.AppAlert) {
                        window.AppAlert.success(data.message || 'Parameter usulan berhasil diperbarui.');
                    }
                    if (data.proposal) {
                        this.selectedProposal = data.proposal;
                    }
                    this.isRevising = false;
                    this.$dispatch('proposal-revised', { id, proposal: data.proposal });
                } else {
                    if (window.AppAlert) {
                        window.AppAlert.error(data.message || 'Gagal memperbarui usulan.');
                    }
                }
            } catch (err) {
                if (window.AppAlert) {
                    window.AppAlert.error('Terjadi kesalahan jaringan: ' + err.message);
                }
            } finally {
                this.isSubmitting = false;
                this.actionInProgressId = null;
            }
        },

        async approveProposal(id) {
            if (this.isSubmitting) return;

            let confirmed = true;
            if (window.AppAlert && typeof window.AppAlert.confirm === 'function') {
                confirmed = await window.AppAlert.confirm({
                    title: 'Setujui & Eksekusi Aksi?',
                    message: this.isRevising 
                        ? 'Aksi akan disetujui dan dieksekusi dengan parameter kuantitas/harga yang telah Anda sesuaikan.' 
                        : 'Aksi ini akan dieksekusi langsung ke sistem bisnis dan dicatat dalam audit trail.',
                    type: 'warning',
                    confirmText: 'Ya, Eksekusi Sekarang',
                    cancelText: 'Batal'
                });
            }

            if (!confirmed) return;

            this.isSubmitting = true;
            this.actionInProgressId = id;

            try {
                const bodyPayload = this.isRevising ? {
                    payload: {
                        quantity: this.editQuantity,
                        unit_price: this.editUnitPrice,
                        notes: this.editNotes
                    }
                } : {};

                const targetUrl = "{{ route('cooca-ai.actions.approve', ':id') }}".replace(':id', id);
                const res = await fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(bodyPayload)
                });

                const data = await res.json();
                if (data.success) {
                    if (window.AppAlert) {
                        window.AppAlert.success(data.message || 'Aksi berhasil dieksekusi.');
                    }
                    this.processedProposalIds.push(String(id));
                    this.closeReviewModal();
                    this.$dispatch('proposal-updated', { id, status: 'approved' });
                } else {
                    if (window.AppAlert) {
                        window.AppAlert.error(data.message || 'Gagal mengeksekusi aksi.');
                    }
                }
            } catch (err) {
                if (window.AppAlert) {
                    window.AppAlert.error('Terjadi kesalahan jaringan: ' + err.message);
                }
            } finally {
                this.isSubmitting = false;
                this.actionInProgressId = null;
            }
        },

        async rejectProposal(id, title) {
            if (this.isSubmitting) return;

            let confirmed = true;
            if (window.AppAlert && typeof window.AppAlert.confirm === 'function') {
                confirmed = await window.AppAlert.confirm({
                    title: 'Tolak Usulan Aksi?',
                    message: `Apakah Anda yakin ingin menolak usulan "${title}"?`,
                    type: 'danger',
                    confirmText: 'Ya, Tolak Usulan',
                    cancelText: 'Kembali'
                });
            }

            if (!confirmed) return;

            this.isSubmitting = true;
            this.actionInProgressId = id;

            try {
                const targetUrl = "{{ route('cooca-ai.actions.reject', ':id') }}".replace(':id', id);
                const res = await fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason: 'Ditolak oleh pemilik bisnis via Action Center.' })
                });

                const data = await res.json();
                if (data.success) {
                    if (window.AppAlert) {
                        window.AppAlert.success(data.message || 'Usulan aksi ditolak.');
                    }
                    this.processedProposalIds.push(String(id));
                    this.closeReviewModal();
                    this.$dispatch('proposal-updated', { id, status: 'rejected' });
                } else {
                    if (window.AppAlert) {
                        window.AppAlert.error(data.message || 'Gagal menolak aksi.');
                    }
                }
            } catch (err) {
                if (window.AppAlert) {
                    window.AppAlert.error('Terjadi kesalahan jaringan: ' + err.message);
                }
            } finally {
                this.isSubmitting = false;
                this.actionInProgressId = null;
            }
        }
    };
}
</script>
@endsection
