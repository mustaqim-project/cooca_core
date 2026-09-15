@extends('layouts.customer', ['title' => 'Pengaturan Profil Pelanggan'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-bold text-black dark:text-white tracking-tight">Pengaturan Profil</h1>
        <p class="text-[13px] text-black/55 dark:text-white/55">Kelola data pribadi dan alamat pengiriman default akun Anda.</p>
    </div>

    @if($errors->any())
        <div class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12.5px] font-medium flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="bento-card p-6 sm:p-7 space-y-6">
        <form action="{{ route('customer.profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Info Dasar --}}
            <div class="space-y-4">
                <span class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">
                    1. Data Diri
                </span>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                           class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Nomor WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone', $customer->phone) }}" required
                               class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Email (Opsional)
                        </label>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                               class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                </div>
            </div>

            {{-- Alamat Pengiriman --}}
            <div class="space-y-3 pt-2 border-t border-black/5 dark:border-white/5">
                <span class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">
                    2. Alamat Pengiriman Utama
                </span>

                <div>
                    <textarea name="shipping_address" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kota, kode pos..."
                              class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none">{{ old('shipping_address', $customer->shipping_address) }}</textarea>
                    <span class="text-[11px] text-black/45 dark:text-white/45 block mt-0.5">Alamat ini otomatis menjadi tujuan pengiriman kurir toko saat checkout.</span>
                </div>
            </div>

            {{-- Ganti Password --}}
            <div class="space-y-3 pt-2 border-t border-black/5 dark:border-white/5" x-data="{ changePass: false }">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">
                        3. Keamanan Akun
                    </span>
                    <button type="button" @click="changePass = !changePass"
                            class="text-[12px] font-semibold text-[#007AFF] hover:underline"
                            x-text="changePass ? 'Batal Ganti Kata Sandi' : 'Ganti Kata Sandi'">
                    </button>
                </div>

                <div x-show="changePass" x-cloak class="space-y-3 p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Kata Sandi Saat Ini
                        </label>
                        <input type="password" name="current_password" placeholder="Masukkan kata sandi saat ini"
                               class="w-full h-10 px-3.5 rounded-[12px] bg-white dark:bg-black/20 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                Kata Sandi Baru
                            </label>
                            <input type="password" name="new_password" minlength="6" placeholder="Min. 6 karakter"
                                   class="w-full h-10 px-3.5 rounded-[12px] bg-white dark:bg-black/20 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                Ulangi Kata Sandi Baru
                            </label>
                            <input type="password" name="new_password_confirmation" minlength="6" placeholder="Ulangi kata sandi baru"
                                   class="w-full h-10 px-3.5 rounded-[12px] bg-white dark:bg-black/20 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full h-11 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm active:scale-[0.98]">
                <span>Simpan Perubahan Profil</span>
                <i data-lucide="check" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

</div>
@endsection
