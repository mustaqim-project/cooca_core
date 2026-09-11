@php
    $status = (int) ($exception?->getStatusCode() ?? 400);
    $content = match ($status) {
        401 => ['title' => 'Sesi diperlukan', 'message' => 'Silakan masuk terlebih dahulu untuk melanjutkan.', 'homeLabel' => 'Masuk'],
        403 => ['title' => 'Akses ditolak', 'message' => 'Anda tidak memiliki izin untuk mengakses atau menjalankan tindakan ini.', 'homeLabel' => 'Ke Dashboard'],
        404 => ['title' => 'Halaman tidak ditemukan', 'message' => 'Alamat yang Anda buka tidak tersedia atau datanya sudah dipindahkan.', 'homeLabel' => 'Ke Dashboard'],
        419 => ['title' => 'Sesi kedaluwarsa', 'message' => 'Halaman ini sudah terlalu lama terbuka. Muat ulang halaman lalu coba lagi.', 'homeLabel' => 'Muat Ulang'],
        429 => ['title' => 'Terlalu banyak permintaan', 'message' => 'Permintaan sedang dibatasi sementara. Tunggu sebentar lalu coba lagi.', 'homeLabel' => 'Coba Lagi'],
        default => ['title' => 'Permintaan tidak dapat diproses', 'message' => 'Permintaan ini tidak dapat diproses. Periksa data Anda lalu coba lagi.', 'homeLabel' => 'Ke Dashboard'],
    };
    $homeUrl = $status === 401 ? route('login') : (Route::has('dashboard') ? route('dashboard') : url('/'));
    if ($status === 419) $homeUrl = url()->current();
    if ($status === 429) $homeUrl = url()->current();
    $reference = substr(sha1(request()->method().'|'.request()->path().'|'.now()->timestamp), 0, 10);
@endphp

@include('errors.layout', [
    'code' => $status,
    'title' => $content['title'],
    'message' => $content['message'],
    'homeUrl' => $homeUrl,
    'homeLabel' => $content['homeLabel'],
    'reference' => $reference,
])
