<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased overflow-hidden">

    {{-- DESKTOP OVERLAY --}}
    <div class="hidden md:flex fixed inset-0 z-[99999] bg-gray-900 items-center justify-center p-6 text-center">
        <div class="max-w-md w-full bg-gray-800 rounded-[3rem] p-10 border-2 border-gray-700 shadow-[0_35px_60px_-15px_rgba(0,0,0,0.8)] flex flex-col items-center space-y-6">
            <div class="w-24 h-24 bg-orange-500 rounded-[2rem] flex items-center justify-center rotate-12 shadow-inner">
                <span class="text-5xl -rotate-12">📱</span>
            </div>
            <div>
                <h1 class="text-3xl font-black text-white uppercase italic tracking-tighter mb-2">Acesso Mobile</h1>
                <p class="text-gray-400 font-medium text-sm leading-relaxed">
                    O Sistema Movvia funciona como um aplicativo nativo e foi otimizado exclusivamente para smartphones. Acesse pelo seu celular.
                </p>
            </div>
        </div>
    </div>

    {{-- GUEST CONTENT --}}
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 md:hidden overflow-y-auto">
        <div>
            <a href="/" wire:navigate>
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

</html>