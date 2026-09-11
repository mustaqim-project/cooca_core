<!DOCTYPE html>
<html lang="id" class="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Buat Kata Sandi Baru Admin — Cooca UMKM Platform</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={darkMode:'class',theme:{extend:{fontFamily:{sans:['-apple-system','"SF Pro Text"','Inter','system-ui','sans-serif']}}}}</script>
<script src="https://unpkg.com/lucide@latest"></script>
<style>body{font-family:-apple-system,"SF Pro Text",Inter,system-ui,sans-serif;-webkit-font-smoothing:antialiased}.sheet-material{background:rgba(255,255,255,.96);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px)}.dark .sheet-material{background:rgba(44,44,46,.96)}</style>
</head>
<body class="min-h-screen bg-[#F2F2F7] dark:bg-[#1E1E1E] flex items-center justify-center p-4 text-black dark:text-white antialiased">
<div class="w-full max-w-[400px]">
  <div class="text-center space-y-2 mb-6">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-[22%] bg-[#007AFF] shadow-[0_4px_16px_rgba(0,122,255,0.3)] mb-3"><i data-lucide="key-round" class="w-8 h-8 text-white" stroke-width="1.5"></i></div>
    <h1 class="text-[24px] font-bold tracking-tight">Atur Sandi Baru Admin</h1>
    <p class="text-[13px] text-black/50 dark:text-white/50">Buat kata sandi baru yang kuat untuk akun administrator Anda</p>
  </div>
  <div class="sheet-material rounded-[20px] p-7 shadow-[0_20px_50px_rgba(0,0,0,0.12)] border border-black/5 dark:border-white/10 space-y-5">
    @if($errors->any())
      <div class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A] text-[13px] flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4 shrink-0" stroke-width="1.5"></i><span>{{ $errors->first() }}</span></div>
    @endif
    <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div>
        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Alamat Email Administrator</label>
        <input type="email" name="email" value="{{ old('email', $email) }}" required readonly class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black/50 dark:text-white/50 cursor-not-allowed">
      </div>
      <div>
        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Kata Sandi Baru</label>
        <input type="password" name="password" required autofocus placeholder="Minimal 8 karakter" class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
      </div>
      <div>
        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Konfirmasi Kata Sandi Baru</label>
        <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru" class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
      </div>
      <button type="submit" class="w-full h-11 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 text-white text-[15px] font-semibold transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="check-circle" class="w-4 h-4" stroke-width="1.5"></i><span>Simpan Kata Sandi & Login</span></button>
    </form>
    <div class="text-center pt-2 border-t border-black/5 dark:border-white/10">
      <a href="{{ route('admin.login') }}" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1.5"><i data-lucide="arrow-left" class="w-3.5 h-3.5" stroke-width="1.5"></i><span>Kembali ke Halaman Login</span></a>
    </div>
  </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{lucide.createIcons()})</script>
</body>
</html>