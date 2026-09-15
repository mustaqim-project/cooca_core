@php
    $status = 419;
    $title = 'Sesi Telah Kedaluwarsa';
    $message = 'Halaman ini sudah terlalu lama tidak aktif atau token sesi telah berakhir. Silakan muat ulang halaman lalu coba kembali.';
    $homeLabel = 'Muat Ulang Halaman';
    $homeUrl = url()->previous() ?: route('landing');
    $reference = substr(sha1(request()->method().'|'.request()->path().'|'.now()->timestamp), 0, 10);
@endphp

@extends('errors.layout', [
    'code' => $status,
    'title' => $title,
    'message' => $message,
    'homeUrl' => $homeUrl,
    'homeLabel' => $homeLabel,
    'reference' => $reference,
])
