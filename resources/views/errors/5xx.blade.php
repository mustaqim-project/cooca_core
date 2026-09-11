@php
    $status = (int) ($exception?->getStatusCode() ?? 500);
    $content = match ($status) {
        503 => ['title' => 'Sistem sedang dipelihara', 'message' => 'Layanan sedang diperbarui. Silakan coba lagi dalam beberapa saat.', 'homeLabel' => 'Muat Ulang'],
        default => ['title' => 'Terjadi kesalahan', 'message' => 'Sistem mengalami kendala saat memproses permintaan. Silakan coba lagi.', 'homeLabel' => 'Ke Dashboard'],
    };
    $homeUrl = $status === 503 ? url()->current() : (Route::has('dashboard') ? route('dashboard') : url('/'));
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
