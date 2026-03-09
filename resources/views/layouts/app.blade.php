<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Movvia</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- LEAFLET CORE --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- LEAFLET ROUTING MACHINE --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden !important;
            overscroll-behavior-y: contain;
            background: #f3f4f6;
        }

        body {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }

        .leaflet-container {
            background: #f3f4f6;
            cursor: crosshair !important;
        }

        .leaflet-routing-container {
            display: none !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="font-sans antialiased h-screen overflow-hidden bg-gray-100 m-0 p-0 relative">

    {{-- BLOQUEIO APENAS PARA TELAS GRANDES --}}
    <div class="hidden xl:flex fixed inset-0 z-[99999] bg-gray-900 items-center justify-center p-6 text-center">
        <div
            class="max-w-md w-full bg-gray-800 rounded-[3rem] p-10 border-2 border-gray-700 shadow-[0_35px_60px_-15px_rgba(0,0,0,0.8)] flex flex-col items-center space-y-6">
            <div class="w-24 h-24 bg-orange-500 rounded-[2rem] flex items-center justify-center rotate-12 shadow-inner">
                <span class="text-5xl -rotate-12">📱</span>
            </div>

            <div>
                <h1 class="text-3xl font-black text-white uppercase italic tracking-tighter mb-2">
                    Acesso Mobile
                </h1>
                <p class="text-gray-400 font-medium text-sm leading-relaxed">
                    O sistema Movvia foi otimizado para celular e tablet.
                    Para a melhor experiência, acesse em um dispositivo móvel.
                </p>
            </div>
        </div>
    </div>

    {{-- APP CONTENT --}}
    <div class="flex flex-col h-full w-full xl:hidden">
        <main class="flex-1 relative overflow-hidden flex flex-col">
            {{ $slot }}
        </main>
    </div>

    {{-- AUDIO ASSETS --}}
    <audio id="snd-online" src="{{ asset('assets/sounds/online.mp3') }}" preload="auto"></audio>
    <audio id="snd-offline" src="{{ asset('assets/sounds/offline.mp3') }}" preload="auto"></audio>

    <script>
        window.playStatusSound = (status) => {
            const sound = document.getElementById(`snd-${status}`);
            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(e => console.log('Erro ao tocar som:', e));
            }
        };

        const setVh = () => {
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        };

        window.addEventListener('resize', setVh);
        setVh();
    </script>

    @stack('scripts')
</body>

</html>