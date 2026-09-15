@extends('layouts.public_marketing', ['title' => 'Lengkapi Profil Usaha - Cooca UMKM', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-xl">
            <!-- Apple HIG Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-[20px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] mb-3.5 shadow-sm">
                    <i data-lucide="sparkles" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Lengkapi Profil Bisnis</h1>
                <p class="mt-2 text-sm text-black/60 dark:text-white/60">Informasi ini akan terpasang di struk kasir, faktur, dan sistem notifikasi toko Anda</p>
            </div>

            <!-- Apple HIG Card -->
            <div
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-9 shadow-2xl shadow-black/5 dark:shadow-black/50 relative overflow-hidden transition-all">
                @if ($errors->any())
                    <div
                        class="mb-6 p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-sm animate-shake">
                        <div class="font-semibold mb-1.5 flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                            <span>Ada data yang perlu diperbaiki:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.complete.save') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name"
                            class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-2">
                            Nama Lengkap Pemilik / Penanggung Jawab <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                required
                                class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none"
                                placeholder="Contoh: Budi Pratama">
                        </div>
                    </div>

                    <div>
                        <label for="phone"
                            class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-2">
                            Nomor WhatsApp / HP Aktif <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                <i data-lucide="smartphone" class="w-5 h-5"></i>
                            </div>
                            <input type="text" name="phone" id="phone"
                                value="{{ old('phone', $user->phone ?? ($business->phone ?? '')) }}" required autofocus
                                class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none"
                                placeholder="Contoh: 081234567890">
                        </div>
                        <p class="text-[11px] sm:text-xs text-black/50 dark:text-white/50 mt-1.5">
                            Digunakan untuk konfirmasi pesanan, struk digital pelanggan, & pemulihan akun jika lupa sandi.
                        </p>
                    </div>

                    <div>
                        <label for="business_name"
                            class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-2">
                            Nama Usaha / Toko / Brand <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                <i data-lucide="store" class="w-5 h-5"></i>
                            </div>
                            <input type="text" name="business_name" id="business_name"
                                value="{{ old('business_name', str_starts_with($business->name ?? '', 'Usaha ') ? '' : $business->name ?? '') }}"
                                required
                                class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none"
                                placeholder="Contoh: Kopi Kenangan Manis / Dapur Ibu">
                        </div>
                        <p class="text-[11px] sm:text-xs text-black/50 dark:text-white/50 mt-1.5">
                            Nama ini akan otomatis tercetak di bagian atas setiap struk kasir & faktur penjualan.
                        </p>
                    </div>

                    <!-- Reassurance / Trust Card -->
                    <div
                        class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] text-xs text-black/65 dark:text-white/65 flex items-start gap-2.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                        <p class="leading-relaxed">
                            Data bisnis Anda terisolasi aman dengan sistem enkripsi berstandar perbankan. Anda dapat
                            mengubah nama toko atau menambah cabang kapan saja di menu Pengaturan.
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 flex items-center justify-center gap-2.5 transition-all active:scale-[0.98]">
                            <span>Simpan & Buka Workspace Bisnis</span>
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
