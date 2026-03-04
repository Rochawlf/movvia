<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Movvia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <style>
        /* Reset Radical: Nada de scroll no body */
        body,
        html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden !important;
        }

        .leaflet-container {
            background: #f3f4f6;
        }
    </style>
</head>
{{-- resources/views/layouts/app.blade.php --}}

<body class="font-sans antialiased h-screen overflow-hidden bg-gray-100 m-0 p-0">
    <div class="flex flex-col h-full w-full">
        <livewire:layout.navigation />
        <main class="flex-1 relative overflow-hidden">
            {{ $slot }}
        </main>
    </div>

    {{-- AUDIO ASSETS --}}
    <audio id="snd-online" src="/sounds/online.mp3" preload="auto"></audio>
    <audio id="snd-offline" src="/sounds/offline.mp3" preload="auto"></audio>

    <script>
        // Função global para tocar os sons
        window.playStatusSound = (status) => {
            const sound = document.getElementById(`snd-${status}`);
            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(e => console.log('Erro ao tocar som:', e));
            }
        };
    </script>
</body>

</html>