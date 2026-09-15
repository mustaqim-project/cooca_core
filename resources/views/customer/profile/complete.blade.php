@extends('layouts.customer', ['title' => 'Lengkapi Profil'])

@section('content')
<div class="max-w-md mx-auto py-6 sm:py-16">
    <div class="bento-card p-8 sm:p-10 space-y-7">

        {{-- Header --}}
        <div class="text-center space-y-3">
            <div class="w-16 h-16 rounded-3xl bg-[#FF9500]/10 border border-[#FF9500]/20 mx-auto flex items-center justify-center text-3xl">
                👤
            </div>
            <h1 class="text-2xl font-extrabold text-black dark:text-white">Lengkapi Profil</h1>
            <p class="text-sm text-black/50 dark:text-white/50 leading-relaxed">
                Tambahkan nomor WhatsApp agar kami bisa mengirim OTP, konfirmasi pesanan, dan informasi pengiriman.
            </p>
        </div>

        {{-- Alerts --}}
        @if($errors->any())
            <div class="p-4 bg-[#FF3B30]/10 border border-[#FF3B30]/20 rounded-2xl text-sm text-[#FF3B30] space-y-1">
                @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif
        @if(session('info'))
            <div class="p-4 bg-[#007AFF]/10 border border-[#007AFF]/20 rounded-2xl text-sm text-[#007AFF]">{{ session('info') }}</div>
        @endif

        {{-- Profile info (read-only from Google) --}}
        <div class="flex items-center gap-3 p-4 bg-black/[0.03] dark:bg-white/[0.03] rounded-2xl">
            @if($customer->avatar_url)
                <img src="{{ $customer->avatar_url }}" class="w-12 h-12 rounded-2xl object-cover" alt="{{ $customer->name }}">
            @else
                <div class="w-12 h-12 rounded-2xl bg-[#007AFF]/10 flex items-center justify-center text-xl">👤</div>
            @endif
            <div>
                <p class="font-bold text-[14px] text-black dark:text-white">{{ $customer->name }}</p>
                <p class="text-sm text-black/50 dark:text-white/50">{{ $customer->email }}</p>
            </div>
            <span class="ml-auto px-2 py-1 bg-[#34C759]/10 text-[#34C759] text-[11px] font-bold rounded-full border border-[#34C759]/20">
                Google
            </span>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('customer.profile.complete.save') }}" class="space-y-5">
            @csrf

            {{-- Phone --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-2">
                    Nomor WhatsApp <span class="text-[#FF3B30]">*</span>
                </label>
                <div class="flex">
                    <span class="inline-flex items-center px-3 rounded-l-xl border-y border-l border-black/10 dark:border-white/10 bg-black/[0.03] dark:bg-white/[0.03] text-sm font-semibold text-black/50 dark:text-white/50">
                        +62
                    </span>
                    <input type="tel" name="phone"
                           value="{{ old('phone', ltrim($customer->phone ?? '', '62')) }}"
                           placeholder="812-3456-7890"
                           class="flex-1 px-4 py-3 rounded-r-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm focus:outline-none focus:border-[#007AFF]/60 focus:ring-2 focus:ring-[#007AFF]/20 transition-all">
                </div>
                @error('phone')
                    <p class="text-[#FF3B30] text-sm mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- Shipping Address (optional) --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-2">
                    Alamat Pengiriman <span class="text-black/30">(opsional)</span>
                </label>
                <textarea name="shipping_address" rows="3" placeholder="Jl. Contoh No. 1, Kota..."
                          class="w-full px-4 py-3 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm focus:outline-none focus:border-[#007AFF]/60 focus:ring-2 focus:ring-[#007AFF]/20 transition-all resize-none">{{ old('shipping_address', $customer->shipping_address) }}</textarea>
            </div>

            <button type="submit"
                    class="w-full py-4 bg-[#007AFF] text-white text-[15px] font-bold rounded-2xl hover:bg-[#0062CC] active:scale-[0.98] transition-all shadow-lg shadow-[#007AFF]/30">
                Simpan & Lanjutkan Verifikasi →
            </button>
        </form>

    </div>
</div>
@endsection