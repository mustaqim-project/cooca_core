@extends('layouts.app', ['title' => $type === 'bugs' ? 'Laporan Bug' : 'Request Fitur'])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><p class="text-xs uppercase tracking-widest text-cyan-400 font-bold">Dukungan Produk</p><h1 class="text-2xl font-black text-white">{{ $type === 'bugs' ? 'Laporan Bug' : 'Request Fitur' }}</h1><p class="text-xs text-slate-400 mt-1">Pantau status dan perkembangan laporan untuk {{ $business->name }}.</p></div>
        <div class="flex gap-2"><a href="{{ route('feedback.bugs.index') }}" class="px-3 py-2 rounded-xl text-xs {{ $type === 'bugs' ? 'bg-cyan-500 text-slate-950' : 'bg-slate-800 text-slate-300' }}">Bug</a><a href="{{ route('feedback.features.index') }}" class="px-3 py-2 rounded-xl text-xs {{ $type === 'features' ? 'bg-amber-400 text-slate-950' : 'bg-slate-800 text-slate-300' }}">Request Fitur</a><a href="{{ route($type === 'bugs' ? 'feedback.bugs.create' : 'feedback.features.create') }}" class="px-4 py-2 rounded-xl bg-emerald-500 text-slate-950 text-xs font-bold">+ Buat Baru</a></div>
    </div>
    @forelse($items as $item)
        <a href="{{ route($type === 'bugs' ? 'feedback.bugs.show' : 'feedback.features.show', $item) }}" class="block glass-card rounded-2xl p-5 border border-slate-800 hover:border-cyan-500/40 transition">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3"><div><div class="flex items-center gap-2"><h2 class="font-bold text-white">{{ $item->title }}</h2><span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-300">{{ str_replace('_', ' ', ucfirst($item->status)) }}</span></div><p class="text-xs text-slate-400 mt-2 line-clamp-2">{{ $item->description }}</p></div><span class="text-[11px] text-slate-500">{{ $item->created_at->format('d M Y H:i') }}</span></div>
            <div class="mt-4 flex items-center gap-3"><div class="flex-1 h-2 bg-slate-800 rounded-full overflow-hidden"><div class="h-full bg-cyan-400" style="width: {{ $item->progress_percent }}%"></div></div><span class="text-xs font-mono text-cyan-300">{{ $item->progress_percent }}%</span></div>
        </a>
    @empty
        <div class="glass-card rounded-2xl p-10 text-center text-sm text-slate-400">Belum ada {{ $type === 'bugs' ? 'laporan bug' : 'request fitur' }}.</div>
    @endforelse
    {{ $items->links() }}
</div>
@endsection
