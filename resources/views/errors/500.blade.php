@php
    $status = 500;
    $title = 'Terjadi Kendala Sistem';
    $message = 'Server mengalami kendala saat memproses permintaan Anda. Tim kami telah mencatat peristiwa ini untuk segera ditangani.';
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
