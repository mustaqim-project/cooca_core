@extends('layouts.app', [
    'title' => 'Pusat SOP Usaha Internal',
    'headerTitle' => 'Pusat SOP & Standar Operasional Usaha',
    'headerSubtitle' => 'Unggah dokumen PDF SOP internal usaha Anda agar AI Assistant dapat memandu staf dan kasir secara otomatis sesuai aturan bisnis Anda.',
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{ uploadModalOpen: false }">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER                                   -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <a href="{{ route('settings.index') }}" class="hover:text-[#007AFF] transition-colors">Pengaturan</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-medium">SOP Internal Usaha</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Pusat SOP &amp; Pengetahuan Usaha</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Dokumen PDF yang diunggah terisolasi 100% aman untuk bisnis Anda dan menjadi sumber pengetahuan AI Assistant.</p>
        </div>

        <div class="flex items-center gap-2 self-stretch sm:self-auto">
            <button type="button" @click="uploadModalOpen = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071EB] text-white text-[13px] font-medium shadow-sm transition-all active:scale-[0.98]">
                <i data-lucide="file-plus" class="w-4 h-4"></i>
                <span>Unggah Dokumen SOP (PDF)</span>
            </button>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. BENTO KPI CARDS                                    -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Dokumen Aktif -->
        <div class="p-5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">Total Dokumen</p>
                <h3 class="text-[24px] font-bold text-black dark:text-white tracking-tight mt-1 tabular-nums">{{ $documents->total() }}</h3>
                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">Dokumen SOP Usaha</p>
            </div>
            <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Card 2: Bagian / Chunks Terindeks -->
        <div class="p-5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">Bagian Terindeks</p>
                <h3 class="text-[24px] font-bold text-black dark:text-white tracking-tight mt-1 tabular-nums">{{ number_format($totalChunks) }}</h3>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Siap Dijawab AI Assistant</p>
            </div>
            <div class="w-10 h-10 rounded-[10px] bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Card 3: Total Halaman -->
        <div class="p-5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">Total Halaman</p>
                <h3 class="text-[24px] font-bold text-black dark:text-white tracking-tight mt-1 tabular-nums">{{ number_format($totalPages) }}</h3>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Halaman Panduan Ekstraksi</p>
            </div>
            <div class="w-10 h-10 rounded-[10px] bg-amber-500/10 text-amber-500 flex items-center justify-center">
                <i data-lucide="book-open" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Card 4: Isolasi Multi-Tenant -->
        <div class="p-5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">Isolasi Privasi</p>
                <h3 class="text-[16px] font-semibold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Terkunci Aman</span>
                </h3>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Khusus Tenant {{ $business->name }}</p>
            </div>
            <div class="w-10 h-10 rounded-[10px] bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                <i data-lucide="lock" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. GUIDANCE BANNER                                    -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-gradient-to-r from-[#007AFF]/10 via-indigo-500/5 to-transparent border border-[#007AFF]/20 p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-[10px] bg-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-[14px] font-semibold text-black dark:text-white tracking-tight">Bagaimana AI Assistant Mempelajari SOP Anda?</h4>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed mt-0.5 max-w-3xl">
                    Setiap dokumen PDF yang diunggah akan diekstraksi teksnya, dipecah menjadi bagian-bagian terstruktur, dan diindeks secara otomatis. Saat karyawan atau kasir Anda bertanya di AI Assistant, sistem akan langsung menjawab sesuai SOP usaha Anda lengkap dengan nomor halaman aslinya.
                </p>
            </div>
        </div>
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-ai-assistant', { detail: { scope: 'sop' } }))" class="px-4 py-2 rounded-[9px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5 text-[13px] font-medium text-black dark:text-white shadow-xs transition-colors shrink-0 flex items-center gap-2">
            <i data-lucide="message-square" class="w-4 h-4 text-[#007AFF]"></i>
            <span>Coba Tanya Asisten</span>
        </button>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DOCUMENTS LIST TABLE                               -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="files" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                <h3 class="text-[15px] font-semibold text-black dark:text-white tracking-tight">Daftar Dokumen SOP Internal</h3>
            </div>
            <span class="text-[12px] text-black/40 dark:text-white/40 tabular-nums">{{ $documents->total() }} Berkas</span>
        </div>

        @if($documents->isEmpty())
            <div class="py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto mb-3 text-black/40 dark:text-white/40">
                    <i data-lucide="file-x" class="w-6 h-6"></i>
                </div>
                <h4 class="text-[15px] font-semibold text-black dark:text-white">Belum Ada Dokumen SOP</h4>
                <p class="text-[13px] text-black/50 dark:text-white/50 max-w-md mx-auto mt-1 mb-4">
                    Unggah dokumen SOP operasional usaha Anda (PDF) agar staf baru maupun kasir dapat belajar secara mandiri lewat chat asisten.
                </p>
                <button type="button" @click="uploadModalOpen = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-[9px] bg-[#007AFF] text-white text-[13px] font-medium shadow-xs hover:bg-[#0071EB] transition-colors">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    <span>Unggah SOP Sekarang</span>
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-black/50 dark:text-white/50 font-medium border-b border-black/5 dark:border-white/5">
                        <tr>
                            <th class="px-6 py-3">Nama Dokumen &amp; Berkas</th>
                            <th class="px-6 py-3">Ukuran File</th>
                            <th class="px-6 py-3">Halaman</th>
                            <th class="px-6 py-3">Bagian Terindeks</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Pengunggah</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @foreach($documents as $doc)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-[8px] bg-red-500/10 text-red-500 flex items-center justify-center shrink-0 mt-0.5">
                                            <i data-lucide="file-text" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-black dark:text-white">{{ $doc->title }}</p>
                                            <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5 flex items-center gap-1">
                                                <i data-lucide="paperclip" class="w-3 h-3"></i>
                                                <span>{{ $doc->file_name }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-black/70 dark:text-white/70 tabular-nums">
                                    {{ $doc->formatted_file_size }}
                                </td>
                                <td class="px-6 py-4 text-black/70 dark:text-white/70 tabular-nums">
                                    {{ $doc->total_pages }} Hal
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-medium text-[11px] tabular-nums">
                                        <i data-lucide="layers" class="w-3 h-3"></i>
                                        {{ $doc->total_chunks }} Bagian
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($doc->status === 'ready')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-medium text-[11px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Siap Digunakan
                                        </span>
                                    @elseif($doc->status === 'processing')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 font-medium text-[11px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Sedang Mengekstrak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-red-500/10 text-red-600 dark:text-red-400 font-medium text-[11px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            Gagal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-black/60 dark:text-white/60 text-[12px]">
                                    {{ $doc->user->name ?? 'Owner' }}<br>
                                    <span class="text-[11px] opacity-70">{{ $doc->created_at->format('d M Y, H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('settings.sop.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen SOP ini? Seluruh bagian terindeks pada asisten akan dihapus.');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-[7px] text-black/40 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Hapus Dokumen">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($documents->hasPages())
                <div class="px-6 py-4 border-t border-black/5 dark:border-white/5">
                    {{ $documents->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. UPLOAD MODAL SHEET (Bento Apple HIG XXL)           -->
    <!-- ===================================================== -->
    <div x-show="uploadModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="w-full max-w-xl bg-white dark:bg-[#1C1C1E] rounded-[20px] shadow-2xl border border-black/10 dark:border-white/10 overflow-hidden"
             @click.outside="uploadModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">Unggah Dokumen SOP Baru</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Format didukung: Dokumen PDF (Maksimal 20 MB)</p>
                    </div>
                </div>
                <button type="button" @click="uploadModalOpen = false" class="text-black/40 hover:text-black dark:hover:text-white p-1 rounded-md">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('settings.sop.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                
                <div>
                    <label class="block text-[13px] font-medium text-black dark:text-white mb-1.5">Judul Dokumen SOP <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: SOP Pembukaan Toko &amp; Kasir Cabang" class="w-full px-3.5 py-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Beri judul yang jelas agar mudah dikenali oleh bot asisten.</p>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black dark:text-white mb-1.5">Berkas PDF <span class="text-red-500">*</span></label>
                    <div class="border-2 border-dashed border-black/15 dark:border-white/15 rounded-[14px] p-6 text-center hover:border-[#007AFF]/50 transition-colors bg-black/[0.01] dark:bg-white/[0.01]">
                        <i data-lucide="file-up" class="w-8 h-8 mx-auto text-black/30 dark:text-white/30 mb-2"></i>
                        <p class="text-[13px] font-medium text-black dark:text-white">Pilih berkas PDF SOP</p>
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Teks dalam PDF akan otomatis diekstrak per bab/halaman</p>
                        <input type="file" name="sop_file" required accept=".pdf" class="mt-3 block w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-medium file:bg-[#007AFF] file:text-white hover:file:bg-[#0071EB] cursor-pointer">
                    </div>
                </div>

                <div class="p-3.5 rounded-[10px] bg-amber-500/10 border border-amber-500/20 text-[12px] text-amber-800 dark:text-amber-300 flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                    <span>Dokumen ini sepenuhnya bersifat privat dan hanya digunakan untuk menjawab pertanyaan staf/kasir yang terdaftar pada bisnis <strong>{{ $business->name }}</strong>.</span>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 rounded-[9px] bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 text-[13px] font-medium text-black dark:text-white transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-[9px] bg-[#007AFF] hover:bg-[#0071EB] text-white text-[13px] font-medium shadow-sm transition-colors flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Mulai Ingest &amp; Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
