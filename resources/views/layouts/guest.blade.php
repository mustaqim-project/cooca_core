@extends('layouts.public_marketing', [
    'title' => $title ?? 'Cooca UMKM - Business Operating System',
    'noindex' => true,
])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-8 sm:py-14 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </div>
@endsection
