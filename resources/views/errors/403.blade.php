@php
    $status = 403;
    $title = 'Akses Ditolak';
    $message = 'Anda tidak memiliki izin atau otorisasi untuk mengakses halaman atau tindakan ini.';
    $homeLabel = Route::has('dashboard') ? 'Ke Dashboard' : 'Ke Beranda';
    $homeUrl = Route::has('dashboard') ? route('dashboard') : route('landing');
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
