@extends('layouts.app', [
    'title' => 'Analitik & Performa Media Sosial - ' . $business->name,
    'headerTitle' => 'Analitik & Wawasan Media Sosial',
    'headerSubtitle' => 'Pantau pertumbuhan jangkauan dan interaksi pelanggan pada konten toko Anda',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="socialInsightsManager()">

        {{-- 0. BREADCRUMB --}}
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <span>›</span>
            <a href="{{ route('social-media.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">Media Sosial</a>
            <span>›</span>
            <span class="text-black/80 dark:text-white/80 font-medium">Analitik &amp; Performa</span>
        </nav>

        {{-- 1. PAGE HEADER --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 shadow-sm">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                        <span>Official Insights API</span>
                    </span>
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#007AFF]/10 text-[#007AFF]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                        <span>Aggregated Metrics</span>
                    </span>
                </div>
                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    Performa Postingan &amp; Keterlibatan Audiens
                </h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Statistik resmi Meta Graph API untuk menganalisis konten mana yang paling diminati oleh pelanggan toko Anda.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button @click="window.location.reload()"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Segarkan Data</span>
                </button>
            </div>
        </header>

        {{-- 2. MODULE NAVIGATION SUB-TABS --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-2.5 flex items-center justify-between shadow-sm">
            <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto overflow-x-auto text-[13px] font-medium">
                <a href="{{ route('social-media.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="link" class="w-4 h-4"></i>
                    <span>Koneksi Akun</span>
                </a>
                <a href="{{ route('social-media.posts.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="image" class="w-4 h-4"></i>
                    <span>Posting Konten</span>
                </a>
                <a href="{{ route('social-media.inbox.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span>Kotak Masuk &amp; Komentar</span>
                </a>
                <a href="{{ route('social-media.insights.index') }}"
                    class="h-8 px-4 rounded-[9px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Analitik &amp; Performa</span>
                </a>
            </div>
        </div>

        {{-- 3. BENTO KPI GRID --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- Impressions --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">Tayangan</span>
                    <i data-lucide="eye" class="w-4 h-4 text-[#007AFF]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_impressions']) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">Total tayangan feed</div>
            </div>

            {{-- Reach --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">Jangkauan</span>
                    <i data-lucide="users" class="w-4 h-4 text-[#34C759]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_reach']) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">Akun unik terjangkau</div>
            </div>

            {{-- Engagement --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">Interaksi</span>
                    <i data-lucide="zap" class="w-4 h-4 text-[#FF9500]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_engagement']) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">Total respon &amp; aksi</div>
            </div>

            {{-- Likes --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">Suka / Reaksi</span>
                    <i data-lucide="heart" class="w-4 h-4 text-[#FF2D55]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_likes']) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">Apresiasi pelanggan</div>
            </div>

            {{-- Comments --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">Komentar</span>
                    <i data-lucide="message-circle" class="w-4 h-4 text-[#5856D6]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_comments']) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">Diskusi &amp; pertanyaan</div>
            </div>

            {{-- Shares --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">Dibagikan</span>
                    <i data-lucide="share-2" class="w-4 h-4 text-[#AF52DE]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_shares']) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">Viralitas konten</div>
            </div>
        </div>

        {{-- 4. POST PERFORMANCE TABLE --}}
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                <div>
                    <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Performa 15 Postingan Terakhir</h2>
                    <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">Metrik langsung ditarik dari Graph API resmi Meta</p>
                </div>
            </div>

            @if($posts->isEmpty())
                <div class="py-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                        <i data-lucide="bar-chart" class="w-6 h-6"></i>
                    </div>
                    <div class="text-[14px] font-bold text-black dark:text-white">Belum Ada Data Analitik</div>
                    <p class="text-[12.5px] text-black/60 dark:text-white/60 max-w-sm mx-auto">
                        Publikasikan postingan pertama Anda untuk mulai mengumpulkan wawasan performa.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-black/5 dark:border-white/10 text-[11.5px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                <th class="pb-3 pl-2">Postingan</th>
                                <th class="pb-3 px-3">Saluran</th>
                                <th class="pb-3 px-3 text-right">Tayangan</th>
                                <th class="pb-3 px-3 text-right">Jangkauan</th>
                                <th class="pb-3 px-3 text-right">Suka</th>
                                <th class="pb-3 px-3 text-right">Komentar</th>
                                <th class="pb-3 px-3 text-right">Bagikan</th>
                                <th class="pb-3 pr-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[13px]">
                            @foreach($posts as $post)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3.5 pl-2 max-w-xs sm:max-w-md">
                                        <div class="font-medium text-black dark:text-white line-clamp-2">
                                            {{ $post->content }}
                                        </div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                            {{ $post->published_at ? $post->published_at->format('d M Y, H:i') : $post->created_at->format('d M Y, H:i') }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-bold
                                            {{ $post->platform === 'facebook' ? 'bg-[#1877F2]/10 text-[#1877F2]' : ($post->platform === 'instagram' ? 'bg-[#E1306C]/10 text-[#E1306C]' : 'bg-black/10 dark:bg-white/10 text-black dark:text-white') }}">
                                            <span>{{ ucfirst($post->platform) }}</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-impressions-{{ $post->id }}">
                                        {{ number_format($post->getMetric('impressions')) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-reach-{{ $post->id }}">
                                        {{ number_format($post->getMetric('reach')) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-likes-{{ $post->id }}">
                                        {{ number_format($post->getMetric('likes')) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-comments-{{ $post->id }}">
                                        {{ number_format($post->getMetric('comments')) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-shares-{{ $post->id }}">
                                        {{ number_format($post->getMetric('shares')) }}
                                    </td>
                                    <td class="py-3.5 pr-2 text-right whitespace-nowrap">
                                        <button @click="syncPostInsights('{{ $post->id }}')"
                                            class="h-7 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.97] transition-all inline-flex items-center gap-1">
                                            <i data-lucide="refresh-cw" class="w-3 h-3" id="icon-sync-{{ $post->id }}"></i>
                                            <span>Tarik Live</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <script>
        function socialInsightsManager() {
            return {
                async syncPostInsights(postId) {
                    const icon = document.getElementById(`icon-sync-${postId}`);
                    if (icon) icon.classList.add('animate-spin');

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch(`/social-media/insights/${postId}/sync`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });

                        const data = await res.json();
                        if (data.success && data.metrics) {
                            const m = data.metrics;
                            if (document.getElementById(`metric-impressions-${postId}`)) {
                                document.getElementById(`metric-impressions-${postId}`).textContent = (m.impressions || 0).toLocaleString();
                            }
                            if (document.getElementById(`metric-reach-${postId}`)) {
                                document.getElementById(`metric-reach-${postId}`).textContent = (m.reach || 0).toLocaleString();
                            }
                            if (document.getElementById(`metric-likes-${postId}`)) {
                                document.getElementById(`metric-likes-${postId}`).textContent = (m.likes || 0).toLocaleString();
                            }
                            if (document.getElementById(`metric-comments-${postId}`)) {
                                document.getElementById(`metric-comments-${postId}`).textContent = (m.comments || 0).toLocaleString();
                            }
                            if (document.getElementById(`metric-shares-${postId}`)) {
                                document.getElementById(`metric-shares-${postId}`).textContent = (m.shares || 0).toLocaleString();
                            }
                            alert('Metrik live berhasil diperbarui.');
                        } else {
                            alert(data.error || 'Gagal memperbarui metrik.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan saat menyinkronkan data.');
                    } finally {
                        if (icon) icon.classList.remove('animate-spin');
                    }
                }
            };
        }
    </script>
@endsection
