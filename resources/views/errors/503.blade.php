@php
    $status = 503;
    $title = 'Sistem Sedang Dipelihara';
    $message =
        'Layanan Cooca sedang menjalani pemeliharaan rutin untuk peningkatan kinerja dan keamanan. Mohon coba kembali beberapa saat lagi.';
    $homeLabel = 'Muat Ulang Halaman';
    $homeUrl = url()->current();
    $reference = substr(sha1(request()->method() . '|' . request()->path() . '|' . now()->timestamp), 0, 10);
@endphp

@extends('errors.layout', [
    'code' => $status,
    'title' => $title,
    'message' => $message,
    'homeUrl' => $homeUrl,
    'homeLabel' => $homeLabel,
    'reference' => $reference,
])
