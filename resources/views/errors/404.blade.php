@php
    $status = 404;
    $title = 'Halaman Tidak Ditemukan';
    $message = 'Alamat yang Anda buka tidak tersedia atau datanya sudah dipindahkan.';
    $homeLabel = 'Ke Beranda';
    $homeUrl = route('landing');
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
