<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EvChargeHub') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                background-color: #0d1527 !important;
                color: #f1f5f9 !important;
                font-family: 'Figtree', sans-serif;
            }
        </style>
        @stack('styles')
    </head>
    <body class="font-sans antialiased min-h-screen flex flex-col">
        @include('layouts.navigation')

        @isset($header)
            <header class="bg-slate-900/80 border-b border-slate-800 shadow-sm">
                <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="flex-grow py-6">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Pustaka HTML5-QRCode -->
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        @stack('scripts')
    </body>
</html>