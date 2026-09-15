@php
    $status = 429;
    $title = 'Terlalu Banyak Permintaan';
    $message = 'Sistem mendeteksi terlalu banyak permintaan dalam waktu singkat demi menjaga keamanan. Silakan tunggu 1-2 menit lalu coba lagi.';
    $homeLabel = 'Coba Lagi';
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
